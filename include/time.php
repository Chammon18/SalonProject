<?php

function formatDuration($time)
{
    if (!$time) return '';

    list($h, $m) = explode(':', $time);

    $text = [];

    if ((int)$h > 0) {
        $text[] = (int)$h . ' hour' . ((int)$h > 1 ? 's' : '');
    }

    if ((int)$m > 0) {
        $text[] = (int)$m . ' min';
    }

    return implode(' ', $text);
}
