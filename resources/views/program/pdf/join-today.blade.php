@if($join ?? false)
  <div class="join button back-orange">
    <strong>
      <a class="text-uppercase text-white" href="https://advplus.ae/#join">Join today</a>
    </strong>
  </div>

  <style>
    .button {
      font-size: {{ 9 * $ratio }}px;
      text-align: center;
      height: {{ 20 * $ratio }}px;
      width: {{ 56 * $ratio }}px;
      padding: 0 {{ 10 * $ratio }}px;
      border-radius: {{ 15 * $ratio }}px;
    }

    .button a {
      text-decoration: none;
    }
  </style>
@endif
