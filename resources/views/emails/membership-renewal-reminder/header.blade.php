<style>
  @media only screen and (max-width: 480px) {
    .program-logo {
      margin: 0 auto;
    }
  }
</style>
<div style="background-color: {{ $data['header_color'] }}; max-width: 600px; margin: 0px auto;">
  <table align="center" border="0" cellpadding="0" cellspacing="0"
         style="background-color: {{ $data['header_color'] }}; width: 100%; border-collapse: collapse;  "
         bgcolor="{{ $data['header_color'] }}">
    <tbody>
    <tr>
      <td style="direction: ltr; font-size: 0px; text-align: center; border-collapse: collapse;   padding: 18px 0px;"
          align="center">
        <div
          style="max-width: 600px; font-family: Montserrat, sans-serif !important; font-size: 12px; margin: 0px auto;">
          <table align="center" border="0" cellpadding="0" cellspacing="0"
                 style="width: 100%; border-collapse: collapse;  ">
            <tbody>
            <tr>
              <td style="direction: ltr; font-size: 0px; text-align: center; border-collapse: collapse;   padding: 0;"
                  align="center">
                <div class="mj-column-per-50 mj-outlook-group-fix"
                     style="font-size: 0px; text-align: left; direction: ltr; display: inline-block; vertical-align: top; width: 100%; font-family: Montserrat, sans-serif !important;"
                     align="left">
                  <table border="0" cellpadding="0" cellspacing="0" width="100%" style="border-collapse: collapse;  ">
                    <tbody>
                    <tr>
                      <td style="vertical-align: top; border-collapse: collapse;   padding: 0 6px;" valign="top">
                        <table border="0" cellpadding="0" cellspacing="0" width="100%"
                               style="border-collapse: collapse;  ">
                          <tbody>
                          <tr>
                            <td align="center"
                                style="font-size: 0px; word-break: break-word; border-collapse: collapse;   padding: 0px 0%;">
                              <table border="0" cellpadding="0" cellspacing="0"
                                     style="border-collapse: collapse; border-spacing: 0px;  "
                                     class="mj-full-width-mobile">
                                <tbody>
                                <tr>
                                  <td align="left" style="width: 288px; padding:0 20px; border-collapse: collapse;  "
                                      class="mj-full-width-mobile">
                                    <img alt="" height="auto" class="program-logo"
                                         src="{{ $data['header_logo'] }}"
                                         style="display: block; outline: none; text-decoration: none; height: 50px; width: auto; font-size: 16px; line-height: 100%; -ms-interpolation-mode: bicubic; border: 0;"
                                         width="288"></td>
                                </tr>
                                </tbody>
                              </table>
                            </td>
                          </tr>
                          </tbody>
                        </table>
                      </td>
                    </tr>
                    </tbody>
                  </table>
                </div>

                <div class="mj-column-per-50 mj-outlook-group-fix"
                     style="font-size: 0px; text-align: left; direction: ltr; display: inline-block; vertical-align: top; width: 100%; font-family: Montserrat, sans-serif !important;"
                     align="left">
                  <table border="0" cellpadding="0" cellspacing="0" width="100%" style="border-collapse: collapse;  ">
                    <tbody>
                    <tr>
                      <td style="vertical-align: top; border-collapse: collapse;   padding: 0 6px;" valign="top">
                        <table border="0" cellpadding="0" cellspacing="0" width="100%"
                               style="border-collapse: collapse;  ">
                          <tbody>
                          <tr>
                            <td align="center"
                                style="font-size: 0px; word-break: break-word; border-collapse: collapse;   padding: 0px 0%;">
                              <table border="0" cellpadding="0" cellspacing="0"
                                     style="border-collapse: collapse; border-spacing: 0px;  "
                                     class="mj-full-width-mobile">
                                <tbody>
                                <tr>
                                  <td style="width: 288px; border-collapse: collapse; padding:0 20px;"
                                      class="mj-full-width-mobile">
                                    @if(isset($data['show_header_powered']) && $data['show_header_powered'])
                                      <img alt="" height="auto"
                                           src="https://www.dripuploads.com/uploads/image_upload/image/2661081/embeddable_e276f94f-5b4c-472f-9062-74002f992db4.png"
                                           style="display: block; outline: none; text-decoration: none; height: auto; width: 100%; font-size: 16px; line-height: 100%; -ms-interpolation-mode: bicubic; border: 0;"
                                           width="288"></td>
                                  @endif
                                </tr>
                                </tbody>
                              </table>
                            </td>
                          </tr>
                          </tbody>
                        </table>
                      </td>
                    </tr>
                    </tbody>
                  </table>
                </div>

              </td>
            </tr>
            </tbody>
          </table>
        </div>
      </td>
    </tr>
    </tbody>
  </table>
</div>
