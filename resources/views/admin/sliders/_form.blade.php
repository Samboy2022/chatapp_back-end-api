{{-- Shared by create and edit so the two can't drift apart. --}}
@csrf

@if ($errors->any())
    <div class="mb-6 px-4 py-3 bg-red-50 border border-red-200 text-red-800 rounded-xl text-sm">
        <p class="font-semibold mb-1">Please fix the following:</p>
        <ul class="list-disc list-inside space-y-0.5">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

@if (session('error'))
    <div class="mb-6 px-4 py-3 bg-red-50 border border-red-200 text-red-800 rounded-xl text-sm">
        {{ session('error') }}
    </div>
@endif

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

    <!-- Left: content -->
    <div class="lg:col-span-2 space-y-6">

        <div class="bg-white border border-gray-100 rounded-2xl p-5 shadow-sm">
            <h3 class="font-semibold text-gray-900 mb-4">Content</h3>

            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Title</label>
                    <input type="text" name="title" value="{{ old('title', $slider->title) }}"
                           placeholder="e.g. Connect with Farmers"
                           class="w-full px-3 py-2 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-green-200 focus:border-green-600 text-sm">
                    <p class="text-xs text-gray-500 mt-1">Shown in bold over the bottom of the image. Leave blank for an image-only slide.</p>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Subtitle</label>
                    <input type="text" name="subtitle" value="{{ old('subtitle', $slider->subtitle) }}"
                           placeholder="e.g. Chat and share experiences with fellow farmers"
                           class="w-full px-3 py-2 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-green-200 focus:border-green-600 text-sm">
                </div>
            </div>
        </div>

        <div class="bg-white border border-gray-100 rounded-2xl p-5 shadow-sm">
            <h3 class="font-semibold text-gray-900 mb-4">Image</h3>

            @if ($slider->resolved_image_url)
                <div class="mb-4">
                    <p class="text-xs text-gray-500 mb-2">Current image</p>
                    <div class="w-full max-w-md h-40 rounded-xl overflow-hidden bg-gray-100 border border-gray-200">
                        <img src="{{ $slider->resolved_image_url }}" alt=""
                             class="w-full h-full" style="object-fit: {{ $slider->image_fit ?: 'cover' }}">
                    </div>
                </div>
            @endif

            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Upload image
                        @if (!$slider->exists)
                            <span class="text-red-500">*</span>
                        @endif
                    </label>
                    <input type="file" name="image" accept="image/jpeg,image/png,image/webp,image/gif"
                           class="w-full px-3 py-2 border border-gray-200 rounded-xl text-sm file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:bg-green-50 file:text-green-700 file:text-sm file:font-medium">
                    <p class="text-xs text-gray-500 mt-1">
                        JPG, PNG, WebP or GIF, up to 8MB.
                        {{-- Wide images suit the slider's shape; a portrait photo
                             gets cropped hard at these dimensions. --}}
                        A landscape image around 1200×600 works best.
                        @if ($slider->exists)
                            Leave empty to keep the current image.
                        @endif
                    </p>
                </div>

                <div class="relative">
                    <div class="absolute inset-0 flex items-center"><div class="w-full border-t border-gray-100"></div></div>
                    <div class="relative flex justify-center"><span class="px-2 bg-white text-xs text-gray-400">or</span></div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Use an image URL</label>
                    <input type="url" name="image_url_manual" value="{{ old('image_url_manual') }}"
                           placeholder="https://example.com/banner.jpg"
                           class="w-full px-3 py-2 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-green-200 focus:border-green-600 text-sm">
                    <p class="text-xs text-gray-500 mt-1">For an image already hosted elsewhere. An uploaded file takes priority over this.</p>
                </div>
            </div>
        </div>

        <div class="bg-white border border-gray-100 rounded-2xl p-5 shadow-sm">
            <h3 class="font-semibold text-gray-900 mb-4">Tap action <span class="text-xs font-normal text-gray-400">(optional)</span></h3>

            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Link URL</label>
                    <input type="url" name="link_url" value="{{ old('link_url', $slider->link_url) }}"
                           placeholder="https://example.com/page"
                           class="w-full px-3 py-2 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-green-200 focus:border-green-600 text-sm">
                    <p class="text-xs text-gray-500 mt-1">Opens when a user taps the slide. Leave blank to make it decorative.</p>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Button label</label>
                    <input type="text" name="link_label" value="{{ old('link_label', $slider->link_label) }}"
                           placeholder="e.g. Learn more"
                           class="w-full px-3 py-2 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-green-200 focus:border-green-600 text-sm">
                </div>
            </div>
        </div>
    </div>

    <!-- Right: sizing and visibility -->
    <div class="space-y-6">

        <div class="bg-white border border-gray-100 rounded-2xl p-5 shadow-sm">
            <h3 class="font-semibold text-gray-900 mb-4">Size</h3>

            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Height (px)</label>
                    <input type="number" name="height" min="80" max="500"
                           value="{{ old('height', $slider->height) }}"
                           placeholder="{{ \App\Models\Setting::get('slider_height') ?: \App\Models\Slider::DEFAULT_HEIGHT }}"
                           class="w-full px-3 py-2 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-green-200 focus:border-green-600 text-sm">
                    <p class="text-xs text-gray-500 mt-1">
                        Leave blank to use the default
                        ({{ \App\Models\Setting::get('slider_height') ?: \App\Models\Slider::DEFAULT_HEIGHT }}px) set under
                        Settings → Sliders. Between 80 and 500.
                    </p>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Image fit</label>
                    <select name="image_fit"
                            class="w-full px-3 py-2 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-green-200 focus:border-green-600 text-sm">
                        @foreach ($fits as $value => $label)
                            <option value="{{ $value }}" @selected(old('image_fit', $slider->image_fit ?: 'cover') === $value)>
                                {{ $label }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>

        <div class="bg-white border border-gray-100 rounded-2xl p-5 shadow-sm">
            <h3 class="font-semibold text-gray-900 mb-4">Visibility</h3>

            <div class="space-y-4">
                <label class="flex items-center gap-3 cursor-pointer">
                    {{-- Hidden field first so an unchecked box still posts a
                         value; without it "is_active" would simply be absent
                         and the slide could never be turned off from the form. --}}
                    <input type="hidden" name="is_active" value="0">
                    <input type="checkbox" name="is_active" value="1"
                           @checked(old('is_active', $slider->exists ? $slider->is_active : true))
                           class="w-4 h-4 rounded border-gray-300 text-green-700 focus:ring-green-200">
                    <span class="text-sm text-gray-700">Show this slide in the app</span>
                </label>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Position</label>
                    <input type="number" name="sort_order" min="0"
                           value="{{ old('sort_order', $slider->sort_order) }}"
                           class="w-full px-3 py-2 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-green-200 focus:border-green-600 text-sm">
                    <p class="text-xs text-gray-500 mt-1">Lower numbers appear first. You can also drag slides on the list page.</p>
                </div>
            </div>
        </div>

        <div class="flex gap-3">
            <button type="submit"
                    class="flex-1 px-4 py-2.5 bg-green-700 text-white rounded-xl text-sm font-semibold hover:bg-green-800 transition-colors">
                {{ $slider->exists ? 'Save changes' : 'Add slide' }}
            </button>
            <a href="{{ route('admin.sliders.index') }}"
               class="px-4 py-2.5 border border-gray-200 text-gray-700 rounded-xl text-sm font-semibold hover:bg-gray-50 transition-colors">
                Cancel
            </a>
        </div>
    </div>
</div>
