<?php

use App\Actions\UploadCoverImage;
use Illuminate\Support\Facades\Storage;

test('cover image is stored with the extension of its content, not the client filename', function () {
    Storage::fake('public');

    $url = (new UploadCoverImage)->execute(gifPolyglotUpload('x.html'));

    expect($url)->toEndWith('_small.gif')
        ->and(Storage::disk('public')->allFiles('media/img'))
        ->toHaveCount(2)
        ->each->toEndWith('.gif');
});
