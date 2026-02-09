<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8"/>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <meta name="viewport" content="width=400"/>
    <title>Компания отклонена</title>
    <link rel="shortcut icon" href="https://tilda.ws/img/tildafavicon.ico"/>
    @include('email_layouts.email_styles')
</head>
<body cellpadding="0" cellspacing="0"
      style="padding: 0; margin: 0; border: 0; width:100%; -webkit-text-size-adjust:100%; -ms-text-size-adjust:100%; background-color: #efefef;">
<!--allrecords-->
<table id="allrecords" class="t-records" data-tilda-email="yes" data-tilda-project-id="6660033"
       data-tilda-page-id="34027376" data-tilda-page-alias="" cellpadding="0" cellspacing="0"
       style="width:100%; border-collapse:collapse; border-spacing:0; padding:0; margin:0; border:0;">
    <tr>
        <td style="background-color: #efefef; "><!--record_mail-->
            @include('email_layouts.email_header')

            <table id="rec549847936" style="width:100%; border-collapse:collapse; border-spacing:0; margin:0; border:0;"
                   cellpadding="0" cellspacing="0" data-record-type="323">
                <tr>
                    <td style="padding-left:15px; padding-right:15px; ">
                        <table id="recin549847936" class="r"
                               style="margin: 0 auto;background-color:#ffffff;border-spacing: 0;width:600px;"
                               align="center">
                            <tr>
                                <td style="padding-top:30px;padding-bottom:0px;padding-left:30px;padding-right:30px;">
                                    <table valign="top" border="0" cellpadding="0" cellspacing="0" width="100%"
                                           style="width: 100%;">
                                        <tr>
                                            <td style="text-align: center; padding: 0 0 0;">
                                                <div
                                                    style="margin: 0 auto; font-family: Helvetica Neue, Helvetica, Arial, sans-serif; color:#222222;font-size:26px;font-weight:bold;">
                                                    Ваша компания отклонена
                                                </div>
                                            </td>
                                        </tr>
                                    </table>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
            </table><!--/record--><!--record_mail-->
            <table id="rec549847937" style="width:100%; border-collapse:collapse; border-spacing:0; margin:0; border:0;"
                   cellpadding="0" cellspacing="0" data-record-type="329">
                <tr>
                    <td style="padding-left:15px; padding-right:15px; ">
                        <table id="recin549847937" class="r"
                               style="margin: 0 auto;background-color:#ffffff;border-spacing: 0;width:600px;"
                               align="center">
                            <tr>
                                <td style="padding-top:15px;padding-bottom:30px;padding-left:30px;padding-right:30px;">
                                    <table border="0" cellpadding="0" cellspacing="0" width="100%" style="width: 100%;">
                                        <tr>
                                            <td style="text-align: center; padding: 0 0 0;">
                                                <div
                                                    style="margin: 0 auto; font-family: Helvetica Neue, Helvetica, Arial, sans-serif; color:#545454;font-size:15px;line-height:1.45;">
                                                    Ваша организация не прошла проверку. Вы можете посмотреть причину
                                                    отклонения в профиле компании.
                                                </div>
                                            </td>
                                        </tr>
                                    </table>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
            </table><!--/record--><!--record_mail-->
            <table id="rec549847938" style="width:100%; border-collapse:collapse; border-spacing:0; margin:0; border:0;"
                   cellpadding="0" cellspacing="0" data-record-type="618">
                <tr>
                    <td style="padding-left:15px; padding-right:15px; ">
                        <table id="recin549847938" class="r"
                               style="margin: 0 auto;background-color:#ffffff;border-spacing: 0;width:600px;"
                               align="center">
                            <tr>
                                <td style="padding-top:0px;padding-bottom:45px;padding-left:30px;padding-right:30px;">
                                    <!-- Workaround: Calculate border radius for Outlook-->
                                    <table border="0" cellpadding="0" cellspacing="0" width="100%" style="width:100%;"
                                           style="margin: 0 auto;table-layout: fixed;">
                                        <tr>
                                            <td>
                                                <table border="0" cellpadding="0" cellspacing="0"
                                                       style="margin: 0 auto;" align="center">
                                                    <tr>
                                                        <td> <!--[if mso]>
                                                            <v:roundrect xmlns:v="urn:schemas-microsoft-com:vml"
                                                                         xmlns:w="urn:schemas-microsoft-com:office:word"
                                                                         href=""
                                                                         style="height:58px;v-text-anchor:middle;mso-wrap-style:none;mso-position-horizontal:center;"
                                                                         arcsize="15%" stroke="f" fillcolor="#fab80f">
                                                                <w:anchorlock/>
                                                                <center
                                                                    style="text-decoration: none; padding: 17px 36px; font-size: 17px; text-align: center; font-weight: bold; font-family:Helvetica Neue, Helvetica, Arial, sans-serif; width: 100%;color:#212121;">
                                                                    Перейти на сайт
                                                                </center>
                                                            </v:roundrect> <![endif]--> <!--[if !mso]--> <a
                                                                style="display: table-cell; text-decoration: none; padding: 17px 36px; font-size: 17px; text-align: center; font-weight: bold; font-family:Helvetica Neue, Helvetica, Arial, sans-serif; width: 100%;color:#212121; border:0px solid ; background-color:#fab80f; border-radius: 5px;"
                                                                href="{{url("/profile/company")}}"> Перейти на сайт </a>
                                                            <!--[endif]-->
                                                        </td>
                                                    </tr>
                                                </table>
                                            </td>
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
