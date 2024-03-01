<svg {{ $attributes->class([]) }}
     xmlns="http://www.w3.org/2000/svg" width="{{ $width }}" height="{{ $height }}"
     xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px"
     viewBox="0 0 100 100" xml:space="preserve">
    <path fill="{{ $color }}"
          d="M 98.293 50 C 98.293 23.334 76.666 1.707 50 1.707 C 23.334 1.707 1.707 23.334 1.707 50 M 9.896 50 C 9.896 27.953 27.743 9.896 50 9.896 C 72.257 9.896 90.104 27.953 90.104 50">
      <animateTransform
        attributeName="transform"
        attributeType="XML"
        type="rotate"
        dur="1s"
        from="0 50 50"
        to="360 50 50"
        repeatCount="indefinite"/>
    </path>
</svg>
