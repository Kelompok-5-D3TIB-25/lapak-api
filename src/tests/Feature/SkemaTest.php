<?php

use Illuminate\Support\Facades\Schema;

it('membuat tabel hasil migration', function () {
    expect(Schema::hasTable('migrations'))->toBeTrue();
});