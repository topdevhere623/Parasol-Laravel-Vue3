<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport"
        content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
  <meta http-equiv="X-UA-Compatible" content="ie=edge">
  <title>Document</title>
</head>

<body>
<form action="{{ $formUrl }}" method="post" id="form" style="display: none">
  @foreach($formData as $name => $value)
    <input type="text" name="{{ $name }}" value="{{ $value }}"/>
  @endforeach
</form>
</body>
<script>
  document.getElementById('form').submit()
</script>
</html>
