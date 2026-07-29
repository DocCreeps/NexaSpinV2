<?php

namespace App\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class ModeCard extends Component
{
    public object $mode;
    public string $detectedColor;
    public array $hoverClasses;

    public function __construct(object $mode)
    {
        $this->mode = $mode;
        $this->detectedColor = $this->resolveColor($mode->color ?? '');
        $this->hoverClasses = $this->resolveHoverClasses();
    }

    private function resolveColor(string $colorString): string
    {
        $color = trim($colorString);

        if (str_contains($color, 'indigo')) return 'indigo';
        if (str_contains($color, 'rose') || str_contains($color, 'pink')) return 'rose';
        if (str_contains($color, 'emerald') || str_contains($color, 'green')) return 'emerald';
        if (str_contains($color, 'amber') || str_contains($color, 'yellow')) return 'amber';
        if (str_contains($color, 'blue') || str_contains($color, 'cyan') || str_contains($color, 'sky')) return 'blue';

        return 'default';
    }

    private function resolveHoverClasses(): array
    {
        if (!$this->mode->available) {
            return ['title' => '', 'button' => ''];
        }

        return match ($this->detectedColor) {
            'indigo' => [
                'title' => 'md:group-hover:text-indigo-600',
                'button' => 'md:group-hover:bg-indigo-600 md:group-hover:text-white md:group-hover:border-transparent',
            ],
            'rose' => [
                'title' => 'md:group-hover:text-rose-600',
                'button' => 'md:group-hover:bg-rose-600 md:group-hover:text-white md:group-hover:border-transparent',
            ],
            'emerald' => [
                'title' => 'md:group-hover:text-emerald-600',
                'button' => 'md:group-hover:bg-emerald-600 md:group-hover:text-white md:group-hover:border-transparent',
            ],
            'amber' => [
                'title' => 'md:group-hover:text-amber-600',
                'button' => 'md:group-hover:bg-amber-600 md:group-hover:text-white md:group-hover:border-transparent',
            ],
            'blue' => [
                'title' => 'md:group-hover:text-blue-600',
                'button' => 'md:group-hover:bg-blue-600 md:group-hover:text-white md:group-hover:border-transparent',
            ],
            default => [
                'title' => 'md:group-hover:text-slate-600',
                'button' => 'md:group-hover:bg-slate-800 md:group-hover:text-white md:group-hover:border-transparent',
            ],
        };
    }

    public function render(): View|Closure|string
    {
        return view('components.mode-card');
    }
}
