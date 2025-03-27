<?php

test('it can ', function () {
    $response = $this->get('/');
    $response->assertStatus(200);
});
