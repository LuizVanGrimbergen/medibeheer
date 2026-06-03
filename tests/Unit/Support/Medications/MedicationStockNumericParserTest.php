<?php

use App\Enums\MedicationDoseUnit;
use App\Support\Medications\MedicationStockNumericParser;

it('parses plain numeric stock', function () {
    $parser = new MedicationStockNumericParser;

    expect($parser->parse('30'))->toBe(30.0);
    expect($parser->parse('3,5', MedicationDoseUnit::PIECE))->toBe(3.5);
});

it('strips unit suffixes from stock', function () {
    $parser = new MedicationStockNumericParser;

    expect($parser->parse('250 ml', MedicationDoseUnit::MILLILITER))->toBe(250.0);
    expect($parser->parse('30 stuks', MedicationDoseUnit::PIECE))->toBe(30.0);
    expect($parser->parse('10 druppels', MedicationDoseUnit::DROP))->toBe(10.0);
});

it('returns null for invalid stock after stripping units', function () {
    $parser = new MedicationStockNumericParser;

    expect($parser->parse('abc stuks', MedicationDoseUnit::PIECE))->toBeNull();
});
