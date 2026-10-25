<?php
declare(strict_types=1);

function section_render(array $data): void
{
    $c = $data['contact'] ?? [];
    $v = $data['home_visits'] ?? [];
    echo form_open('contacto');
    ?>
    <div class="panel">
      <h2 class="panel__title">Clínica</h2>
      <?= field_text('whatsapp', $c['whatsapp'] ?? '', 'WhatsApp de la clínica', ['inputmode' => 'tel', 'required' => true, 'hint' => 'Podés escribirlo como “342 486-0753”: lo pasamos solo al formato de WhatsApp.']) ?>
      <?= field_text('phone_display', $c['phone_display'] ?? '', 'Teléfono (como se muestra)', ['inputmode' => 'tel', 'placeholder' => '342 486-0753']) ?>
      <?= field_text('email', $c['email'] ?? '', 'Email', ['type' => 'email', 'inputmode' => 'email']) ?>
    </div>

    <div class="panel">
      <h2 class="panel__title">Dirección</h2>
      <?= field_text('address', $c['address'] ?? '', 'Dirección', ['maxlength' => 100]) ?>
      <?= field_text('address_note', $c['address_note'] ?? '', 'Aclaración (opcional)', ['maxlength' => 120, 'hint' => 'Ej.: “Frente a la plaza”. Dejalo vacío si no hace falta.']) ?>
      <div class="grid-2">
        <?= field_text('city', $c['city'] ?? '', 'Ciudad', ['maxlength' => 60]) ?>
        <?= field_text('region', $c['region'] ?? '', 'Provincia', ['maxlength' => 60]) ?>
      </div>
      <?= field_text('maps_query', $c['maps_query'] ?? '', 'Dirección para el mapa', ['maxlength' => 160, 'hint' => 'Lo que se buscaría en Google Maps para encontrar la clínica.']) ?>
      <?= field_text('maps_url', $c['maps_url'] ?? '', 'Link de Google Maps (opcional)', ['type' => 'url', 'hint' => 'Si lo pegás, el botón “Cómo llegar” usa este link.']) ?>
      <?= field_text('geo', trim(($c['geo']['lat'] ?? '') . (($c['geo']['lat'] ?? '') !== '' ? ', ' : '') . ($c['geo']['lng'] ?? '')), 'Coordenadas (opcional, ayuda a Google)', ['placeholder' => '-31.6627, -60.7653', 'hint' => 'En Google Maps, tocá y mantené sobre la clínica: arriba aparecen dos números. Copialos y pegalos acá.']) ?>
    </div>

    <div class="panel">
      <h2 class="panel__title">Redes</h2>
      <?= field_text('instagram', $c['instagram'] ?? '', 'Instagram', ['type' => 'url']) ?>
      <?= field_text('facebook', $c['facebook'] ?? '', 'Facebook', ['type' => 'url']) ?>
    </div>

    <div class="panel">
      <h2 class="panel__title">Atención a domicilio</h2>
      <?= field_text('visit_vet', $v['vet'] ?? '', 'Veterinario', ['maxlength' => 80]) ?>
      <?= field_text('visit_phone', $v['phone_display'] ?? '', 'Su WhatsApp / teléfono', ['inputmode' => 'tel', 'placeholder' => '342 613-1922']) ?>
    </div>
    <?php
    echo form_close();
}

function section_save(array $data): array
{
    $wa = normalize_whatsapp(posted('whatsapp'));
    if (strlen($wa) < 12) {
        throw new RuntimeException('El WhatsApp de la clínica no parece completo. Escribilo con característica, ej.: 342 486-0753.');
    }
    $email = clean_line(posted('email'), 160);
    if ($email !== '' && clean_email($email) === '') {
        throw new RuntimeException('El email no tiene un formato válido.');
    }
    $geo = ['lat' => '', 'lng' => ''];
    $geoRaw = clean_line(posted('geo'), 60);
    if ($geoRaw !== '') {
        if (!preg_match('/^\s*(-?\d{1,2}(?:\.\d+)?)\s*[,;\s]\s*(-?\d{1,3}(?:\.\d+)?)\s*$/', $geoRaw, $m)
            || abs((float) $m[1]) > 90 || abs((float) $m[2]) > 180) {
            throw new RuntimeException('Las coordenadas tienen que ser dos números separados por coma, ej.: -31.6627, -60.7653.');
        }
        $geo = ['lat' => $m[1], 'lng' => $m[2]];
    }
    $display = clean_line(posted('phone_display'), 30);
    $visitPhone = clean_line(posted('visit_phone'), 30);

    $data['contact'] = array_merge($data['contact'] ?? [], [
        'whatsapp' => $wa,
        'phone_display' => $display,
        'phone' => tel_from_display($display),
        'email' => $email,
        'address' => clean_line(posted('address'), 100),
        'address_note' => clean_line(posted('address_note'), 120),
        'city' => clean_line(posted('city'), 60),
        'region' => clean_line(posted('region'), 60),
        'maps_query' => clean_line(posted('maps_query'), 160),
        'maps_url' => clean_url(posted('maps_url')),
        'geo' => $geo,
        'instagram' => clean_url(posted('instagram')),
        'facebook' => clean_url(posted('facebook')),
    ]);
    $data['home_visits'] = [
        'vet' => clean_line(posted('visit_vet'), 80),
        'whatsapp' => $visitPhone !== '' ? normalize_whatsapp($visitPhone) : '',
        'phone' => tel_from_display($visitPhone),
        'phone_display' => $visitPhone,
    ];
    // El servicio "Atención a domicilio" usa el número del veterinario.
    foreach ($data['services'] ?? [] as $i => $s) {
        if (($s['id'] ?? '') === 'domicilio') {
            $data['services'][$i]['whatsapp'] = $data['home_visits']['whatsapp'];
        }
    }
    return [$data, '¡Listo! Datos de contacto actualizados.'];
}
