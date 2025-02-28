<?php

declare(strict_types=1);
require __DIR__ . '/../vendor/autoload.php';

use GuzzleHttp\Client;
use GuzzleHttp\Psr7\HttpFactory;
use OpenTelemetry\Contrib\Zipkin\Exporter as ZipkinExporter;
use OpenTelemetry\SDK\Common\Attribute\Attributes;
use OpenTelemetry\SDK\Trace\SpanProcessor\SimpleSpanProcessor;
use OpenTelemetry\SDK\Trace\TracerProvider;

$zipkinExporter = new ZipkinExporter(
    'alwaysOnZipkinExample',
    'http://zipkin:9411/api/v2/spans',
    new Client(),
    new HttpFactory(),
    new HttpFactory()
);
$tracerProvider =  new TracerProvider(
    new SimpleSpanProcessor(
        $zipkinExporter
    )
);
$tracer = $tracerProvider->getTracer();

echo 'Starting AlwaysOnZipkinExample';

$root = $span = $tracer->spanBuilder('root')->startSpan();
$span->activate();

for ($i = 0; $i < 3; $i++) {
    // start a span, register some events
    $span = $tracer->spanBuilder('loop-' . $i)->startSpan();

    $span->setAttribute('remote_ip', '1.2.3.4')
        ->setAttribute('country', 'USA');

    $span->addEvent('found_login' . $i, new Attributes([
        'id' => $i,
        'username' => 'otuser' . $i,
    ]));
    $span->addEvent('generated_session', new Attributes([
        'id' => md5((string) microtime(true)),
    ]));

    $span->end();
}
$root->end();
echo PHP_EOL . 'AlwaysOnZipkinExample complete!  See the results at http://localhost:9411/';

echo PHP_EOL;
