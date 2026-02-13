<?php

use MilliRules\Acorn\ResponseCollector;

beforeEach(function () {
    $this->collector = new ResponseCollector();
});

it('starts with no headers', function () {
    expect($this->collector->getHeaders())->toBe([]);
});

it('starts with no replacement', function () {
    expect($this->collector->hasReplacement())->toBeFalse();
    expect($this->collector->getReplacement())->toBeNull();
});

it('queues headers', function () {
    $this->collector->addHeader('X-Custom', 'value');
    $this->collector->addHeader('X-Another', 'test');

    expect($this->collector->getHeaders())->toBe([
        'X-Custom' => 'value',
        'X-Another' => 'test',
    ]);
});

it('overwrites duplicate headers', function () {
    $this->collector->addHeader('X-Custom', 'first');
    $this->collector->addHeader('X-Custom', 'second');

    expect($this->collector->getHeaders())->toBe([
        'X-Custom' => 'second',
    ]);
});

it('queues a redirect', function () {
    $this->collector->setRedirect('/new-path', 301);

    expect($this->collector->hasReplacement())->toBeTrue();

    $response = $this->collector->getReplacement();
    expect($response)->not->toBeNull();
    expect($response->getStatusCode())->toBe(301);
    expect($response->headers->get('Location'))->toBe('/new-path');
});

it('defaults redirect status to 302', function () {
    $this->collector->setRedirect('/somewhere');

    $response = $this->collector->getReplacement();
    expect($response->getStatusCode())->toBe(302);
});

it('clears all state', function () {
    $this->collector->addHeader('X-Test', 'value');
    $this->collector->setRedirect('/url');

    $this->collector->clear();

    expect($this->collector->getHeaders())->toBe([]);
    expect($this->collector->hasReplacement())->toBeFalse();
    expect($this->collector->getReplacement())->toBeNull();
});
