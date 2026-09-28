<?php

namespace App\Support;

/**
 * Landscape A4 PDF writer for large tables. DomPDF runs out of memory
 * on thousands of issuance rows; this writes Helvetica text line by line.
 */
class SimplePdf
{
    protected float $width = 842.0;

    protected float $height = 595.0;

    /** @var list<string> */
    protected array $pages = [];

    protected string $buffer = '';

    protected float $y = 0;

    protected bool $open = false;

    public function title(string $title, string $subtitle = ''): void
    {
        $this->ensurePage();
        $this->write(24, $this->y, $title, 13);
        $this->y -= 16;
        if ($subtitle !== '') {
            $this->write(24, $this->y, $subtitle, 9);
            $this->y -= 14;
        }
        $this->y -= 4;
    }

    /**
     * @param  list<string>  $headers
     * @param  list<list<string>>  $rows
     * @param  list<float>  $widths
     */
    public function table(array $headers, array $rows, array $widths): void
    {
        $this->ensurePage();
        if ($this->y < 60) {
            $this->newPage();
        }
        $this->headerRow($headers, $widths);

        foreach ($rows as $row) {
            if ($this->y < 36) {
                $this->newPage();
                $this->headerRow($headers, $widths);
            }
            $this->dataRow($row, $widths);
        }
    }

    public function paragraph(string $text): void
    {
        $this->ensurePage();
        if ($this->y < 48) {
            $this->newPage();
        }
        $this->write(24, $this->y, $text, 9);
        $this->y -= 12;
    }

    public function output(): string
    {
        $this->flushPage();
        if ($this->pages === []) {
            $this->ensurePage();
            $this->flushPage();
        }

        $objects = [];
        $objects[1] = '<< /Type /Catalog /Pages 2 0 R >>';
        $objects[3] = '<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica /Encoding /WinAnsiEncoding >>';

        $pageIds = [];
        $next = 4;
        foreach ($this->pages as $content) {
            $contentId = $next++;
            $pageId = $next++;
            $objects[$contentId] = '<< /Length '.strlen($content)." >>\nstream\n".$content.(str_ends_with($content, "\n") ? '' : "\n")."endstream";
            $objects[$pageId] = sprintf(
                '<< /Type /Page /Parent 2 0 R /MediaBox [0 0 %.2F %.2F] /Resources << /Font << /F1 3 0 R >> >> /Contents %d 0 R >>',
                $this->width,
                $this->height,
                $contentId
            );
            $pageIds[] = $pageId;
        }

        $kids = implode(' ', array_map(fn (int $id) => $id.' 0 R', $pageIds));
        $objects[2] = sprintf('<< /Type /Pages /Kids [%s] /Count %d >>', $kids, count($pageIds));
        ksort($objects);

        $pdf = "%PDF-1.4\n";
        $offsets = [0];
        foreach ($objects as $id => $body) {
            $offsets[$id] = strlen($pdf);
            $pdf .= $id." 0 obj\n".$body."\nendobj\n";
        }

        $xref = strlen($pdf);
        $max = max(array_keys($objects));
        $pdf .= "xref\n0 ".($max + 1)."\n";
        $pdf .= "0000000000 65535 f \n";
        for ($i = 1; $i <= $max; $i++) {
            $pdf .= sprintf("%010d 00000 n \n", $offsets[$i] ?? 0);
        }
        $pdf .= "trailer << /Size ".($max + 1)." /Root 1 0 R >>\nstartxref\n{$xref}\n%%EOF\n";

        return $pdf;
    }

    protected function ensurePage(): void
    {
        if (! $this->open) {
            $this->newPage();
        }
    }

    protected function newPage(): void
    {
        $this->flushPage();
        $this->open = true;
        $this->buffer = '';
        $this->y = $this->height - 32;
    }

    protected function flushPage(): void
    {
        if (! $this->open) {
            return;
        }
        $this->pages[] = $this->buffer;
        $this->open = false;
        $this->buffer = '';
    }

    /**
     * @param  list<string>  $headers
     * @param  list<float>  $widths
     */
    protected function headerRow(array $headers, array $widths): void
    {
        $this->dataRow($headers, $widths, 8);
        $this->y -= 2;
    }

    /**
     * @param  list<string>  $cells
     * @param  list<float>  $widths
     */
    protected function dataRow(array $cells, array $widths, int $size = 8): void
    {
        $x = 24.0;
        foreach ($widths as $i => $width) {
            $this->write($x, $this->y, (string) ($cells[$i] ?? ''), $size, $width - 4);
            $x += $width;
        }
        $this->y -= 11;
    }

    protected function write(float $x, float $y, string $text, int $size = 9, ?float $maxWidth = null): void
    {
        $text = $this->winAnsi($text);
        if ($maxWidth !== null) {
            $text = $this->fit($text, $maxWidth, $size);
        }
        $this->buffer .= sprintf("BT /F1 %d Tf %.2F %.2F Td (%s) Tj ET\n", $size, $x, $y, $this->escape($text));
    }

    protected function fit(string $text, float $maxWidth, int $size): string
    {
        $maxChars = max(4, (int) floor($maxWidth / ($size * 0.5)));
        if (strlen($text) <= $maxChars) {
            return $text;
        }

        return rtrim(substr($text, 0, $maxChars - 2)).'..';
    }

    protected function winAnsi(string $text): string
    {
        $text = str_replace(['₱', '—', '–', '’', '‘', '“', '”'], ['P', '-', '-', "'", "'", '"', '"'], $text);
        $converted = @iconv('UTF-8', 'Windows-1252//TRANSLIT//IGNORE', $text);

        return is_string($converted) ? $converted : preg_replace('/[^\x20-\x7E]/', '?', $text) ?? $text;
    }

    protected function escape(string $text): string
    {
        return str_replace(['\\', '(', ')'], ['\\\\', '\\(', '\\)'], $text);
    }
}
