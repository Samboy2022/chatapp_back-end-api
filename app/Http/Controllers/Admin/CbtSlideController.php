<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CbtSlide;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

/** Admin for the banners at the top of the app's CBT Practice screen. */
class CbtSlideController extends Controller
{
    public function index(): View
    {
        return view('admin.cbt.slides.index', ['slides' => CbtSlide::ordered()->get()]);
    }

    public function create(): View
    {
        return view('admin.cbt.slides.create', [
            'slide' => new CbtSlide(['is_active' => true]),
            'targets' => $this->targets(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);
        $data['sort_order'] ??= (int) CbtSlide::max('sort_order') + 1;
        CbtSlide::create($data);

        return redirect()->route('admin.cbt.slides.index')->with('success', 'Slide added.');
    }

    public function edit(CbtSlide $slide): View
    {
        return view('admin.cbt.slides.edit', ['slide' => $slide, 'targets' => $this->targets()]);
    }

    public function update(Request $request, CbtSlide $slide): RedirectResponse
    {
        $slide->update($this->validated($request));

        return redirect()->route('admin.cbt.slides.index')->with('success', 'Slide updated.');
    }

    public function destroy(CbtSlide $slide): RedirectResponse
    {
        $slide->delete();

        return back()->with('success', 'Slide deleted.');
    }

    public function toggleActive(CbtSlide $slide): RedirectResponse
    {
        $slide->update(['is_active' => !$slide->is_active]);

        return back()->with('success', 'Slide is now ' . ($slide->is_active ? 'visible' : 'hidden') . '.');
    }

    /** Button targets offered in the form: each exam, or a web link. */
    private function targets(): array
    {
        return collect(config('cbt.exams'))->map(fn ($e) => 'Open ' . $e['title'])->all();
    }

    private function validated(Request $request): array
    {
        $data = $request->validate([
            'badge' => ['nullable', 'string', 'max:30'],
            'title' => ['required', 'string', 'max:80'],
            'subtitle' => ['nullable', 'string', 'max:200'],
            'cta_label' => ['nullable', 'string', 'max:30'],
            'cta_kind' => ['required', Rule::in(['none', 'exam', 'url'])],
            'cta_exam' => ['nullable', Rule::in(array_keys(config('cbt.exams')))],
            'cta_url' => ['nullable', 'url', 'max:2048'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
        ]);

        $target = match ($data['cta_kind']) {
            'exam' => $data['cta_exam'] ?? null,
            'url' => $data['cta_url'] ?? null,
            default => null,
        };

        return [
            'badge' => $data['badge'] ?? null,
            'title' => $data['title'],
            'subtitle' => $data['subtitle'] ?? null,
            'cta_label' => $target ? (($data['cta_label'] ?? null) ?: 'Begin Now') : null,
            'cta_target' => $target,
            'sort_order' => $data['sort_order'] ?? null,
            'is_active' => $request->boolean('is_active'),
        ];
    }
}
