<?php
// Verificação reproduzível da feature "Verificador de adimplência" (POST /api/verificacao).
// O back não tem suíte automatizada; este script faz o papel dela.
//
// Uso (na raiz do repo back, com o servidor em http://localhost:8000):
//   php tests/verificacao_adimplencia.php            roda tudo e remove os dados de teste
//   php tests/verificacao_adimplencia.php --keep     roda tudo e DEIXA os sócios de teste no banco LOCAL
//   php tests/verificacao_adimplencia.php --cleanup  só remove os dados de teste
//   API=http://localhost:8000 php tests/...          muda a URL base
//
// Mexe APENAS no banco apontado pelo .env (local). Nunca rode contra produção.

require_once __DIR__ . '/../src/config.php';

use Database\Database;
use Util\AdimplenciaSocio;
use Util\Cpf;

const CPF_ADIMPLENTE   = '529.982.247-25'; // CPFs válidos (dígitos verificadores corretos)
const CPF_INADIMPLENTE = '111.444.777-35';
const CPF_INATIVO      = '390.533.447-05';
const CPF_NAO_SOCIO    = '168.995.350-09';

$api = rtrim(getenv('API') ?: 'http://localhost:8000', '/') . '/api';
$pdo = Database::getConnection();
$falhas = 0;
$total = 0;

function check(string $nome, bool $ok, string $detalhe = ''): void
{
    global $falhas, $total;
    $total++;
    if (!$ok) {
        $falhas++;
    }
    echo ($ok ? '[OK]    ' : '[FALHA] ') . $nome . ($ok ? '' : "  -> $detalhe") . PHP_EOL;
}

function http(string $metodo, string $url, ?array $corpo = null, array $cabecalhos = []): array
{
    $h = array_merge(['Content-Type: application/json'], $cabecalhos);
    $ctx = stream_context_create(['http' => [
        'method' => $metodo,
        'header' => implode("\r\n", $h),
        'content' => $corpo === null ? '' : json_encode($corpo),
        'ignore_errors' => true,
        'timeout' => 10,
    ]]);
    $raw = @file_get_contents($url, false, $ctx);
    $status = 0;
    $headers = [];
    foreach ($http_response_header ?? [] as $linha) {
        if (preg_match('#^HTTP/\S+ (\d+)#', $linha, $m)) {
            $status = (int) $m[1];
        } else {
            $headers[] = $linha;
        }
    }
    return ['status' => $status, 'json' => json_decode((string) $raw, true), 'raw' => (string) $raw, 'headers' => $headers];
}

function limparFixtures(PDO $pdo): void
{
    $cpfs = [CPF_ADIMPLENTE, CPF_INADIMPLENTE, CPF_INATIVO];
    foreach ($cpfs as $cpf) {
        $pdo->prepare("DELETE m FROM mensalidades m JOIN socios s ON s.id = m.socio_id WHERE s.cpf = :c")->execute([':c' => $cpf]);
        $pdo->prepare("DELETE FROM socios WHERE cpf = :c")->execute([':c' => $cpf]);
    }
}

function limparRateLimit(PDO $pdo): void
{
    $pdo->exec("DELETE FROM verificacao_tentativas");
}

function criarSocio(PDO $pdo, string $nome, string $cpf, string $status, string $entrada): int
{
    $pdo->prepare("INSERT INTO socios (nome_completo, cpf, status, data_entrada) VALUES (:n, :c, :s, :e)")
        ->execute([':n' => $nome, ':c' => $cpf, ':s' => $status, ':e' => $entrada]);
    return (int) $pdo->lastInsertId();
}

function pagar(PDO $pdo, int $socioId, DateTimeImmutable $de, DateTimeImmutable $ate, array $pular = []): void
{
    for ($d = $de; $d <= $ate; $d = $d->modify('first day of next month')) {
        $ym = $d->format('Y-m');
        $status = in_array($ym, $pular, true) ? 'Atrasado' : 'Pago';
        $pdo->prepare("INSERT INTO mensalidades (socio_id, mes, ano, valor, status, data_vencimento) VALUES (:s, :m, :a, 50, :st, :v)")
            ->execute([':s' => $socioId, ':m' => (int) $d->format('n'), ':a' => (int) $d->format('Y'), ':st' => $status, ':v' => $d->format('Y-m-t')]);
    }
}

if (in_array('--cleanup', $argv, true)) {
    limparFixtures($pdo);
    limparRateLimit($pdo);
    echo "Dados de teste removidos." . PHP_EOL;
    exit(0);
}

// -------------------------------------------------------------------------
echo "== 1. Unidade: CPF ==" . PHP_EOL;
check('CPF valido com mascara', Cpf::validar('529.982.247-25'));
check('CPF valido sem mascara', Cpf::validar('52998224725'));
check('CPF com digito verificador errado reprova (golden)', !Cpf::validar('529.982.247-26'));
check('CPF com 10 digitos reprova', !Cpf::validar('5299822472'));
check('CPF repetido 111.111.111-11 reprova', !Cpf::validar('111.111.111-11'));
check('CPF do seed 476.457.130-70 e valido', Cpf::validar('476.457.130-70'));

echo PHP_EOL . "== 2. Unidade: regra de adimplencia (data fixa 10/11/2026) ==" . PHP_EOL;
$hoje = new DateTimeImmutable('2026-11-10');
$pg = fn(array $ym) => array_map(fn($s) => ['ano' => (int) substr($s, 0, 4), 'mes' => (int) substr($s, 5, 2)], $ym);
$r = AdimplenciaSocio::calcular('Ativo', '2026-01-15', $pg(['2026-07', '2026-08', '2026-09', '2026-10', '2026-11']), $hoje);
check('tudo pago ate o mes atual => adimplente, ate 2026-11', $r['adimplente'] === true && $r['em_dia_ate'] === '2026-11', json_encode($r));
$r = AdimplenciaSocio::calcular('Ativo', '2026-01-15', $pg(['2026-07', '2026-08', '2026-09', '2026-10']), $hoje);
check('mes atual pendente, anteriores pagos => adimplente, ate 2026-10', $r['adimplente'] === true && $r['em_dia_ate'] === '2026-10', json_encode($r));
$r = AdimplenciaSocio::calcular('Ativo', '2026-01-15', $pg(['2026-07', '2026-08', '2026-10', '2026-11']), $hoje);
check('setembro sem pagar => inadimplente', $r['adimplente'] === false, json_encode($r));
$r = AdimplenciaSocio::calcular('Ativo', '2026-01-15', $pg(['2026-07', '2026-08', '2026-09', '2026-10', '2026-11', '2026-12', '2027-01']), $hoje);
check('adiantadas ate jan/2027 => ate 2027-01 (virada de ano)', $r['em_dia_ate'] === '2027-01', json_encode($r));
$r = AdimplenciaSocio::calcular('Inativo', '2026-01-15', $pg(['2026-10', '2026-11']), $hoje);
check('socio Inativo => nao adimplente', $r['adimplente'] === false, json_encode($r));
$r = AdimplenciaSocio::calcular('Ativo', '2026-10-20', $pg(['2026-10']), $hoje);
check('entrou em out/2026: meses antes da entrada nao contam => adimplente', $r['adimplente'] === true, json_encode($r));
$r = AdimplenciaSocio::calcular('Ativo', '2026-01-15', [], new DateTimeImmutable('2026-03-05'));
check('virada de ano no lookback (marco/2026, nada pago) => inadimplente', $r['adimplente'] === false, json_encode($r));
// dados reais do seed (sócio 1): ate abril/2026 so ha jan e fev pagos
$r = AdimplenciaSocio::calcular('Ativo', '2026-01-15', $pg(['2026-01', '2026-02']), new DateTimeImmutable('2026-04-10'));
check('seed socio 1 em 10/04/2026 (marco Atrasado) => inadimplente, igual ao front', $r['adimplente'] === false, json_encode($r));

// -------------------------------------------------------------------------
echo PHP_EOL . "== 3. Integracao HTTP (" . $api . ") ==" . PHP_EOL;
limparFixtures($pdo);
limparRateLimit($pdo);

$agora = new DateTimeImmutable('first day of this month');
$idA = criarSocio($pdo, 'Teste Adimplente Silva', CPF_ADIMPLENTE, 'Ativo', $agora->modify('-8 months')->format('Y-m-d'));
pagar($pdo, $idA, $agora->modify('-7 months'), $agora);
$idI = criarSocio($pdo, 'Teste Inadimplente Souza', CPF_INADIMPLENTE, 'Ativo', $agora->modify('-8 months')->format('Y-m-d'));
pagar($pdo, $idI, $agora->modify('-7 months'), $agora, [$agora->modify('-2 months')->format('Y-m')]);
$idN = criarSocio($pdo, 'Teste Inativo Lima', CPF_INATIVO, 'Inativo', $agora->modify('-8 months')->format('Y-m-d'));
pagar($pdo, $idN, $agora->modify('-7 months'), $agora);
echo "(fixtures criadas: socios $idA, $idI, $idN)" . PHP_EOL;

$mesAtual = $agora->format('Y-m');
$vp = fn(string $cpf) => http('POST', "$api/verificacao", ['cpf' => $cpf]);

$res = $vp(CPF_ADIMPLENTE);
check('CPF adimplente com mascara: 200, adimplente=true, em_dia_ate=mes atual', $res['status'] === 200 && ($res['json']['adimplente'] ?? null) === true && ($res['json']['em_dia_ate'] ?? null) === $mesAtual, $res['raw']);
check('resposta so tem o primeiro nome e campos minimos', ($res['json']['nome'] ?? '') === 'Teste' && array_diff(array_keys($res['json']), ['encontrado', 'nome', 'adimplente', 'verificado_em', 'em_dia_ate']) === [], $res['raw']);
check('resposta nao contem CPF nem sobrenome', !str_contains($res['raw'], '529') && !str_contains($res['raw'], 'Silva'), $res['raw']);
check('Cache-Control: no-store', (bool) array_filter($res['headers'], fn($h) => stripos($h, 'cache-control: no-store') === 0), implode(' | ', $res['headers']));

$res = $vp('52998224725');
check('CPF adimplente SEM mascara tambem acha o socio', $res['status'] === 200 && ($res['json']['adimplente'] ?? null) === true, $res['raw']);

$res = $vp(CPF_INADIMPLENTE);
check('CPF inadimplente: 200, adimplente=false, sem em_dia_ate e sem valores', $res['status'] === 200 && ($res['json']['adimplente'] ?? null) === false && !isset($res['json']['em_dia_ate']) && !str_contains($res['raw'], 'valor'), $res['raw']);

$res = $vp(CPF_INATIVO);
check('socio Inativo: adimplente=false', $res['status'] === 200 && ($res['json']['adimplente'] ?? null) === false, $res['raw']);

$res = $vp(CPF_NAO_SOCIO);
check('CPF valido que nao e socio: 200 {encontrado:false} e nada mais', $res['status'] === 200 && $res['json'] === ['encontrado' => false], $res['raw']);

$res = $vp('529.982.247-26');
check('CPF com digito verificador invalido: 400 "CPF invalido"', $res['status'] === 400 && str_contains($res['raw'], 'CPF inválido'), $res['raw']);
$res = http('POST', "$api/verificacao", []);
check('sem campo cpf: 400 igual ao CPF invalido', $res['status'] === 400, $res['raw']);
$res = http('POST', "$api/verificacao", ['cpf' => "529.982.247-25' OR '1'='1"]);
check('tentativa de SQL injection no cpf: 400 (so digitos validos passam)', $res['status'] === 400, $res['raw']);

echo PHP_EOL . "-- rota publica nao abre as outras --" . PHP_EOL;
$res = http('GET', "$api/socios");
check('GOLDEN: GET /api/socios sem token continua 401', $res['status'] === 401, (string) $res['status']);
$res = http('GET', "$api/relatorios/inadimplentes");
check('GET /api/relatorios/inadimplentes sem token continua 401', $res['status'] === 401, (string) $res['status']);
$res = http('GET', "$api/mensalidades");
check('GET /api/mensalidades sem token continua 401', $res['status'] === 401, (string) $res['status']);
$res = http('GET', "$api/verificacao/1");
check('GET /api/verificacao/1 (subrota) exige token: 401', $res['status'] === 401, (string) $res['status']);
$res = http('GET', "$api/verificacao");
check('GET /api/verificacao: 405 (so POST)', $res['status'] === 405, (string) $res['status']);

echo PHP_EOL . "-- rate limit (10 consultas/min por IP) --" . PHP_EOL;
limparRateLimit($pdo);
$codigos = [];
for ($i = 1; $i <= 12; $i++) {
    $res = $vp(CPF_NAO_SOCIO);
    $codigos[] = $res['status'];
}
echo "status das 12 consultas seguidas: " . implode(',', $codigos) . PHP_EOL;
check('as 10 primeiras passam (200) e a 11a e a 12a dao 429', array_slice($codigos, 0, 10) === array_fill(0, 10, 200) && array_slice($codigos, 10) === [429, 429], implode(',', $codigos));
check('429 traz Retry-After e mensagem amigavel', (bool) array_filter($res['headers'], fn($h) => stripos($h, 'retry-after:') === 0) && str_contains($res['raw'], 'Muitas consultas'), $res['raw']);
$res = http('GET', "$api/socios");
check('rate limit da verificacao nao afeta o restante (GET /socios segue 401, nao 429)', $res['status'] === 401, (string) $res['status']);
$linha = $pdo->query("SELECT ip_hash FROM verificacao_tentativas LIMIT 1")->fetchColumn();
check('tabela guarda hash sha256 do IP (64 hex), nao o IP', is_string($linha) && preg_match('/^[0-9a-f]{64}$/', $linha) === 1, (string) $linha);

limparRateLimit($pdo);
if (!in_array('--keep', $argv, true)) {
    limparFixtures($pdo);
    echo PHP_EOL . "(fixtures removidas)" . PHP_EOL;
} else {
    echo PHP_EOL . "(--keep: sócios de teste mantidos; remova com --cleanup)" . PHP_EOL;
}

echo PHP_EOL . "Resultado: " . ($total - $falhas) . "/$total OK" . ($falhas ? " — $falhas FALHA(S)" : '') . PHP_EOL;
exit($falhas ? 1 : 0);
