<?php

namespace App\Services;

use Dompdf\Dompdf;
use Dompdf\Options;
use Illuminate\Support\Facades\File;

final class PdfFontService
{
    private const WINDOWS_FONT_FILES = [
        'Times New Roman' => ['normal' => 'times.ttf', 'bold' => 'timesbd.ttf', 'italic' => 'timesi.ttf', 'bold_italic' => 'timesbi.ttf'],
        'Arial' => ['normal' => 'arial.ttf', 'bold' => 'arialbd.ttf', 'italic' => 'ariali.ttf', 'bold_italic' => 'arialbi.ttf'],
        'Calibri' => ['normal' => 'calibri.ttf', 'bold' => 'calibrib.ttf', 'italic' => 'calibrii.ttf', 'bold_italic' => 'calibriz.ttf'],
        'Georgia' => ['normal' => 'georgia.ttf', 'bold' => 'georgiab.ttf', 'italic' => 'georgiai.ttf', 'bold_italic' => 'georgiaz.ttf'],
        'Garamond' => ['normal' => 'GARA.TTF', 'bold' => 'GARABD.TTF', 'italic' => 'GARAIT.TTF'],
        'Tahoma' => ['normal' => 'tahoma.ttf', 'bold' => 'tahomabd.ttf'],
        'Trebuchet MS' => ['normal' => 'trebuc.ttf', 'bold' => 'trebucbd.ttf', 'italic' => 'trebucit.ttf', 'bold_italic' => 'trebucbi.ttf'],
        'Verdana' => ['normal' => 'verdana.ttf', 'bold' => 'verdanab.ttf', 'italic' => 'verdanai.ttf', 'bold_italic' => 'verdanaz.ttf'],
    ];

    public static function make(iterable $families): Dompdf
    {
        $fontCache = storage_path('app/dompdf-fonts');
        File::ensureDirectoryExists($fontCache);

        $systemFontDirectory = self::systemFontDirectory();
        $chroot = [public_path(), storage_path()];
        if ($systemFontDirectory !== null) {
            $chroot[] = $systemFontDirectory;
        }

        $options = new Options();
        $options->set('isRemoteEnabled', true);
        $options->set('isFontSubsettingEnabled', true);
        $options->set('defaultMediaType', 'print');
        $options->set('fontDir', $fontCache);
        $options->set('fontCache', $fontCache);
        $options->set('chroot', $chroot);

        $pdf = new Dompdf($options);
        if ($systemFontDirectory !== null) {
            self::registerFamilies($pdf, $systemFontDirectory, $families);
        }

        return $pdf;
    }

    private static function registerFamilies(Dompdf $pdf, string $directory, iterable $families): void
    {
        foreach (collect($families)->filter()->unique() as $family) {
            foreach (self::WINDOWS_FONT_FILES[$family] ?? [] as $type => $filename) {
                $path = $directory.DIRECTORY_SEPARATOR.$filename;
                if (! is_file($path)) {
                    continue;
                }

                [$weight, $style] = match ($type) {
                    'bold' => ['bold', 'normal'],
                    'italic' => ['normal', 'italic'],
                    'bold_italic' => ['bold', 'italic'],
                    default => ['normal', 'normal'],
                };
                // Dompdf removes the literal "file://" prefix before realpath().
                // On Windows the URI therefore must retain the drive letter directly
                // after that prefix (file://C:/...), without an additional slash.
                $uri = 'file://'.str_replace('\\', '/', $path);
                try {
                    $pdf->getFontMetrics()->registerFont(compact('family', 'weight', 'style'), $uri);
                } catch (\Throwable) {
                    // Keep PDF generation available when an optional system font
                    // is missing or stored in an unsupported collection format.
                }
            }
        }
    }

    private static function systemFontDirectory(): ?string
    {
        $windowsDirectory = getenv('WINDIR');
        if ($windowsDirectory !== false && is_dir($windowsDirectory.DIRECTORY_SEPARATOR.'Fonts')) {
            return $windowsDirectory.DIRECTORY_SEPARATOR.'Fonts';
        }

        return null;
    }
}
