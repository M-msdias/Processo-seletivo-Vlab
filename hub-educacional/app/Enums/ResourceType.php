<?php

namespace App\Enums;

enum ResourceType: string
{
    case Video = 'video';
    case PDF   = 'pdf';
    case Link  = 'link';

    public function label(): string
    {
        return match($this) {
            ResourceType::Video => 'Vídeo',
            ResourceType::PDF   => 'Documento PDF',
            ResourceType::Link  => 'Link de Referência',
        };
    }
}
