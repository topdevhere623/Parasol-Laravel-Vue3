<!DOCTYPE html>
<html>
<head>
  <style>
    @font-face {
      font-family: 'Mighty River';
      src: url({{asset('assets/fonts/Mighty_RiverDemo.ttf')}}) format('truetype');
    }

    @font-face {
      font-family: 'Brandon Grotesque';
      src: url({{asset('assets/fonts/BrandonGrotesque-Black.ttf')}}) format('truetype');
      font-weight: 900;
      font-style: normal;
    }

    @font-face {
      font-family: 'Brandon Grotesque';
      src: url({{asset('assets/fonts/BrandonGrotesque-BlackItalic.ttf')}}) format('truetype');
      font-weight: 900;
      font-style: italic;
    }

    @font-face {
      font-family: 'Brandon Grotesque';
      src: url({{asset('assets/fonts/../../assets/fonts/BrandonGrotesque-MediumItalic.ttf')}}) format('truetype');
      font-weight: 500;
      font-style: italic;
    }

    @font-face {
      font-family: 'Brandon Grotesque';
      src: url({{asset('assets/fonts/BrandonGrotesque-Medium.ttf')}}) format('truetype');
      font-weight: 500;
      font-style: normal;
    }

    @font-face {
      font-family: 'Brandon Grotesque';
      src: url({{asset('assets/fonts/BrandonGrotesque-Bold.ttf')}}) format('truetype');
      font-weight: bold;
      font-style: normal;
    }

    @font-face {
      font-family: 'Brandon Grotesque';
      src: url({{asset('assets/fonts/BrandonGrotesque-Light.ttf')}}) format('truetype');
      font-weight: 300;
      font-style: normal;
    }

    @font-face {
      font-family: 'Brandon Grotesque';
      src: url({{asset('assets/fonts/BrandonGrotesque-LightItalic.ttf')}}) format('truetype');
      font-weight: 300;
      font-style: italic;
    }

    @font-face {
      font-family: 'Brandon Grotesque';
      src: url({{asset('assets/fonts/BrandonGrotesque-ThinItalic.ttf')}}) format('truetype');
      font-weight: 100;
      font-style: italic;
    }

    @font-face {
      font-family: 'Brandon Grotesque';
      src: url({{asset('assets/fonts/BrandonGrotesque-Thin.ttf')}}) format('truetype');
      font-weight: 100;
      font-style: normal;
    }

    @font-face {
      font-family: 'Brandon Grotesque';
      src: url({{asset('assets/fonts/BrandonGrotesque-Regular.ttf')}}) format('truetype');
      font-weight: normal;
      font-style: normal;
    }

    @font-face {
      font-family: 'Brandon Grotesque';
      src: url({{asset('assets/fonts/BrandonGrotesque-RegularItalic.ttf')}}) format('truetype');
      font-weight: normal;
      font-style: italic;
    }

    @font-face {
      font-family: 'Brandon Grotesque';
      src: url({{asset('assets/fonts/BrandonGrotesque-BoldItalic.ttf')}}) format('truetype');
      font-weight: bold;
      font-style: italic;
    }

    * {
      margin: 0;
      padding: 0
    }

    html {
      margin: 0;
      padding: 0
    }

    body {
      margin: 0;
      padding: 0;
    }

    @page {
      margin: 0;
    }

  </style>
</head>
<body>
@yield('content')
</body>
</html>
