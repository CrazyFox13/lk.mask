<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8"/>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <meta name="viewport" content="width=400"/>
    <title>Рейтинг вырос</title>
    <link rel="shortcut icon" href="https://tilda.ws/img/tildafavicon.ico"/>
    @include('email_layouts.email_styles')
</head>
<body cellpadding="0" cellspacing="0"
      style="padding: 0; margin: 0; border: 0; width:100%; -webkit-text-size-adjust:100%; -ms-text-size-adjust:100%; background-color: #efefef;">
<!--allrecords-->
<table id="allrecords" class="t-records" data-tilda-email="yes" data-tilda-project-id="6660033"
       data-tilda-page-id="33701992" data-tilda-page-alias="" cellpadding="0" cellspacing="0"
       style="width:100%; border-collapse:collapse; border-spacing:0; padding:0; margin:0; border:0;">
    <tr>
        <td style="background-color: #efefef; "><!--record_mail-->
            @include('email_layouts.email_header')

            <table id="rec544661354" style="width:100%; border-collapse:collapse; border-spacing:0; margin:0; border:0;"
                   cellpadding="0" cellspacing="0" data-record-type="324">
                <tr>
                    <td style="padding-left:15px; padding-right:15px; ">
                        <table id="recin544661354" class="r"
                               style="margin: 0 auto;background-color:#ffffff;border-spacing: 0;width:600px;"
                               align="center">
                            <tr>
                                <td style="padding-top:0px;padding-bottom:30px;padding-left:0;padding-right:0;">
                                    <table border="0" cellpadding="0" cellspacing="0" width="100%">
                                        <tr>
                                            <td style=""><a href="{{url("/")}}"> <img width="600"
                                                                                      style="display: block; width: 100%;"
                                                                                      src="{{url('/images/astt-bg.png')}}">
                                                </a></td>
                                        </tr>
                                        <tr>
                                            <td style="text-align: center; padding: 24px 30px 0;">
                                                <a href="{{url("/")}}" style="text-decoration: none; color: #222222;">
                                                    <div
                                                        style="margin: 0 auto; font-weight: normal; font-family: Helvetica Neue, Helvetica, Arial, sans-serif; color:#222222;font-size:26px;line-height:1.45;max-width:400px; padding:0;">
                                                        <strong>{{$pushNotification->subject}}</strong></div>
                                                </a>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td style="text-align: center; padding: 11px 30px 0;">
                                                <div
                                                    style="margin: 0 auto; font-weight: normal; font-family: Helvetica Neue, Helvetica, Arial, sans-serif; color:#4a4c52;font-size:15px;line-height:1.45; padding:0;">
                                                    {!! $pushNotification->text !!}
                                                </div>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td style="height: 30px;"></td>
                                        </tr>
                                    </table>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
            </table><!--/record--><!--record_mail-->

            @include("email_layouts.email_footer")
        </td>
    </tr>
</table><!--/allrecords--></body>
</html>
