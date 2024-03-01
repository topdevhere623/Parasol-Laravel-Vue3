<div
  class="d-flex align-content-center justify-content-center justify-content-md-start position-relative flex-wrap mt-3">
  <h6 class="w-100 text-muted fs-4 opacity-75 mb-3 text-center text-md-start">{{ $title }}</h6>
  <input type="file" id="{{ $name }}Photo" name="{{ $inputName ?? $name }}[photo]"
         class="image-uploader position-absolute opacity-0"
         accept="image/jpeg, image/png"
         @empty($defaultAvatar) required @endempty>
  <label class="file-uploader" for="{{ $name }}Photo">
    <img src="{{ asset('assets/images/image-upload.svg') }}" alt="">
    <span>Upload</span>
  </label>
  <div class="file-uploader-preview ms-4 {{ $name }}Photo"
       style="@if(!empty($defaultAvatar)) background-image: url({{ $defaultAvatar }}); @else display: none; @endif"></div>
  <div class="mt-3 mt-mt-0 ms-md-4 ">
    <p class="text-center text-md-start">
      The picture will be on your Membership Pass. <br> Ideally, user image with <b>white background</b> and <br> <b>passport
        size</b>
    </p>
    <p class="text-muted">Max. file size: 5MB <br> Supported file types: JPG, PNG</p>
  </div>
</div>
