<?php
/**
 * Perrito de la marca (de perfil, mirando a la derecha). Lo usan la 404 y el
 * easter egg del logo. Las partes tienen clase para animarlas con CSS.
 */

declare(strict_types=1);

function dog_svg(string $class = 'dog'): string
{
    return <<<SVG
<svg class="{$class}" viewBox="0 0 170 120" aria-hidden="true" focusable="false">
  <g class="dog__body-group">
    <path class="dog__tail" d="M34 58c-12-6-18-18-14-30" fill="none" stroke="#2C0C3D" stroke-width="11" stroke-linecap="round"/>
    <path class="dog__tail" d="M34 58c-12-6-18-18-14-30" fill="none" stroke="#FFEFFF" stroke-width="5" stroke-linecap="round"/>
    <rect class="dog__leg dog__leg--back-a" x="40" y="70" width="11" height="30" rx="5.5" fill="#FFEFFF" stroke="#2C0C3D" stroke-width="3"/>
    <rect class="dog__leg dog__leg--back-b" x="54" y="70" width="11" height="30" rx="5.5" fill="#E6D0EC" stroke="#2C0C3D" stroke-width="3"/>
    <rect class="dog__leg dog__leg--front-a" x="96" y="70" width="11" height="30" rx="5.5" fill="#E6D0EC" stroke="#2C0C3D" stroke-width="3"/>
    <rect class="dog__leg dog__leg--front-b" x="110" y="70" width="11" height="30" rx="5.5" fill="#FFEFFF" stroke="#2C0C3D" stroke-width="3"/>
    <rect x="30" y="46" width="96" height="36" rx="18" fill="#FFEFFF" stroke="#2C0C3D" stroke-width="3"/>
    <ellipse cx="66" cy="58" rx="14" ry="9" fill="#E6D0EC"/>
    <g class="dog__head">
      <rect x="108" y="44" width="10" height="22" rx="4" fill="#E8419A" stroke="#2C0C3D" stroke-width="3" transform="rotate(-12 113 55)"/>
      <circle cx="132" cy="38" r="20" fill="#FFEFFF" stroke="#2C0C3D" stroke-width="3"/>
      <ellipse cx="150" cy="46" rx="14" ry="10" fill="#FFEFFF" stroke="#2C0C3D" stroke-width="3"/>
      <circle cx="162" cy="42" r="4.5" fill="#2C0C3D"/>
      <circle cx="136" cy="32" r="3.2" fill="#2C0C3D"/>
      <path d="M120 22c-9 4-12 20-4 30 7-6 9-20 4-30z" fill="#E8419A" stroke="#2C0C3D" stroke-width="3" stroke-linejoin="round"/>
      <path d="M150 54c3 3 7 3 10 0" fill="none" stroke="#2C0C3D" stroke-width="2.5" stroke-linecap="round"/>
    </g>
    <g transform="translate(104 64)">
      <rect x="-5" y="-5" width="10" height="10" fill="none"/>
      <rect x="-1.6" y="-5" width="3.2" height="10" rx="1" fill="#E8419A"/>
      <rect x="-5" y="-1.6" width="10" height="3.2" rx="1" fill="#E8419A"/>
    </g>
  </g>
</svg>
SVG;
}
