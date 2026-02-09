<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8"/>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <meta name="viewport" content="width=400"/>
    <title>Новая заявка</title>
    <link rel="shortcut icon" href="https://tilda.ws/img/tildafavicon.ico"/>
    @include('email_layouts.email_styles')
</head>
<body cellpadding="0" cellspacing="0"
      style="padding: 0; margin: 0; border: 0; width:100%; -webkit-text-size-adjust:100%; -ms-text-size-adjust:100%; background-color: #efefef;">
<!--allrecords-->
<table id="allrecords" class="t-records" data-tilda-email="yes" data-tilda-project-id="6660033"
       data-tilda-page-id="33309537" data-tilda-page-alias="" cellpadding="0" cellspacing="0"
       style="width:100%; border-collapse:collapse; border-spacing:0; padding:0; margin:0; border:0;">
    <tr>
        <td style="background-color: #efefef; "><!--record_mail-->
            @include('email_layouts.email_header')

            <table id="rec538402378" style="width:100%; border-collapse:collapse; border-spacing:0; margin:0; border:0;"
                   cellpadding="0" cellspacing="0" data-record-type="322">
                <tr>
                    <td style="padding-left:15px; padding-right:15px; ">
                        <table id="recin538402378" class="r"
                               style="margin: 0 auto;background-color:#ffffff;border-spacing: 0;width:600px;"
                               align="center">
                            <tr>
                                <td style="padding-top:0px;padding-bottom:0px;padding-left:0;padding-right:0;">
                                    <table border="0" cellpadding="0" cellspacing="0" width="100%" style="width:100%;">
                                        <tr>
                                            <td style="width:100%; "><img width="600" align="center"
                                                                          style="width: 100%; height: auto;"
                                                                          src="{{url('/images/astt-bg-xl.png')}}"
                                                                          imgfield="img"></td>
                                        </tr>
                                    </table>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
            </table><!--/record--><!--record_mail-->
            <table id="rec538399016" style="width:100%; border-collapse:collapse; border-spacing:0; margin:0; border:0;"
                   cellpadding="0" cellspacing="0" data-record-type="323">
                <tr>
                    <td style="padding-left:15px; padding-right:15px; ">
                        <table id="recin538399016" class="r"
                               style="margin: 0 auto;background-color:#ffffff;border-spacing: 0;width:600px;"
                               align="center">
                            <tr>
                                <td style="padding-top:30px;padding-bottom:0px;padding-left:30px;padding-right:30px;">
                                    <table valign="top" border="0" cellpadding="0" cellspacing="0" width="100%"
                                           style="width: 100%;">
                                        <tr>
                                            <td style="text-align: center; padding: 0 0 0;">
                                                <div
                                                    style="margin: 0 auto; font-family: Helvetica Neue, Helvetica, Arial, sans-serif; color:#222222;font-size:26px;line-height:1.35;font-weight:bold;">
                                                    Здравствуйте@if($user)
                                                        , {{$user->getRawOriginal("name")}}
                                                    @endif!
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
            <table id="rec538399017" style="width:100%; border-collapse:collapse; border-spacing:0; margin:0; border:0;"
                   cellpadding="0" cellspacing="0" data-record-type="329">
                <tr>
                    <td style="padding-left:15px; padding-right:15px; ">
                        <table id="recin538399017" class="r"
                               style="margin: 0 auto;background-color:#ffffff;border-spacing: 0;width:600px;"
                               align="center">
                            <tr>
                                <td style="padding-top:15px;padding-bottom:30px;padding-left:30px;padding-right:30px;">
                                    <table border="0" cellpadding="0" cellspacing="0" width="100%" style="width: 100%;">
                                        <tr>
                                            <td style="text-align: left; padding: 0 0 0;">
                                                <div
                                                    style="margin-right: auto; font-family: Helvetica Neue, Helvetica, Arial, sans-serif; color:#4a4c52;font-size:15px;line-height:1.45;">
                                                    <br/>На портале ASTT.SU появилась новая заявка на
                                                    <strong>{{$vehicleTitle}}</strong><br/>Дата начала работ:
                                                    <strong>{{\Carbon\Carbon::parse($order->start_date)->format("d.m.Y")}}</strong><br/><strong></strong><br/>Вы
                                                    можете первым
                                                    предложить свои услуги, подробная информация о заявке и заказчике
                                                    находится на сайте.
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
            <table id="rec538399018" style="width:100%; border-collapse:collapse; border-spacing:0; margin:0; border:0;"
                   cellpadding="0" cellspacing="0" data-record-type="618">
                <tr>
                    <td style="padding-left:15px; padding-right:15px; ">
                        <table id="recin538399018" class="r"
                               style="margin: 0 auto;background-color:#ffffff;border-spacing: 0;width:600px;"
                               align="center">
                            <tr>
                                <td style="padding-bottom:30px;padding-left:30px;padding-right:30px;">
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
                                                                    Посмотреть заявку
                                                                </center>
                                                            </v:roundrect> <![endif]--> <!--[if !mso]--> <a
                                                                style="display: table-cell; text-decoration: none; padding: 17px 36px; font-size: 17px; text-align: center; font-weight: bold; font-family:Helvetica Neue, Helvetica, Arial, sans-serif; width: 100%;color:#212121; border:0px solid ; background-color:#fab80f; border-radius: 5px;"
                                                                href="{{url("/orders/$order->id")}}"> Посмотреть
                                                                заявку </a> <!--[endif]--> </td>
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
            <table id="rec538408457" style="width:100%; border-collapse:collapse; border-spacing:0; margin:0; border:0;"
                   cellpadding="0" cellspacing="0" data-record-type="619">
                <tr>
                    <td style="padding-left:15px; padding-right:15px; ">
                        <table id="recin538408457" class="r" style="margin: 0 auto;border-spacing: 0;width:600px;"
                               align="center">
                            <tr>
                                <td style="padding-top:0px;padding-bottom:0px;padding-left:0;padding-right:0;">
                                    <table valign="top" border="0" cellpadding="0" cellspacing="0" width="100%"
                                           style="table-layout: fixed;">
                                        <tr>
                                            <td style="height:30px;" height="30px"></td>
                                        </tr>
                                    </table>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
            </table><!--/record--><!--record_mail-->
            <table id="rec538412001" style="width:100%; border-collapse:collapse; border-spacing:0; margin:0; border:0;"
                   cellpadding="0" cellspacing="0" data-record-type="645">
                <tr>
                    <td style="padding-left:15px; padding-right:15px; ">
                        <table id="recin538412001" class="r"
                               style="margin: 0 auto;background-color:#ffffff;border-spacing: 0;width:600px;"
                               align="center">
                            <tr>
                                <td style="padding-top:30px;padding-bottom:30px;padding-left:30px;padding-right:30px;">
                                    <table border="0" cellpadding="0" cellspacing="0" width="100%"
                                           style="margin: 0 auto;table-layout: fixed;">
                                        <tr>
                                            <td><a href="{{url("/")}}"> <img width="100"
                                                                             style="display: block; width: 100px; margin: 0 auto;"
                                                                             src="{{url("/images/astt-track.png")}}">
                                                </a></td>
                                        </tr>
                                        <tr>
                                            <td style="text-align: center; padding: 15px 0 0;"><a href="{{url("/")}}"
                                                                                                  style="text-decoration: none; color: #222222;">
                                                    <div
                                                        style="margin: 0 auto; font-weight: normal; font-family: Helvetica Neue, Helvetica, Arial, sans-serif; color:#222222;font-size:17px;line-height:1.45;max-width:400px; padding:0;">
                                                        <strong>Следите за новыми заявками на сайте, получайте только
                                                            актуальные заказы</strong></div>
                                                </a></td>
                                        </tr>
                                        <tr>
                                            <td style="text-align: center; padding: 16px 0 2px;">
                                                <div
                                                    style="margin: 0 auto; font-weight: normal; font-family: Helvetica Neue, Helvetica, Arial, sans-serif; color:#4a4c52;font-size:15px;line-height:1.45; padding:0;">
                                                    В первый час после публикации заявки заказчик получает максимум
                                                    откликов и в это время его шансы найти спецтехнику равны 93%.
                                                </div>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td style="padding: 24px 0 0">
                                                <table border="0" cellpadding="0" cellspacing="0"
                                                       style="margin: 0 auto;" align="center">
                                                    <tr>
                                                        <td> <!--[if mso]>
                                                            <v:roundrect xmlns:v="urn:schemas-microsoft-com:vml"
                                                                         xmlns:w="urn:schemas-microsoft-com:office:word"
                                                                         href="{{url("/")}}"
                                                                         style="height:58px;v-text-anchor:middle;mso-wrap-style:none;mso-position-horizontal:center;"
                                                                         arcsize="15%" strokecolor="#fab80f"
                                                                         fillcolor="#ffffff">
                                                                <w:anchorlock/>
                                                                <center
                                                                    style="text-decoration: none; padding: 17px 36px; font-size: 17px; text-align: center; font-weight: bold; font-family:Helvetica Neue, Helvetica, Arial, sans-serif; width: 100%;color:#222222;">
                                                                    Перейти на сайт
                                                                </center>
                                                            </v:roundrect> <![endif]--> <!--[if !mso]--> <a
                                                                style="display: table-cell; text-decoration: none; padding: 17px 36px; font-size: 17px; text-align: center; font-weight: bold; font-family:Helvetica Neue, Helvetica, Arial, sans-serif; width: 100%;color:#222222; border:2px solid #fab80f; background-color:#ffffff; border-radius: 5px;"
                                                                href="{{url("/")}}"> Перейти на сайт </a>
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
            <!--/record-->
        </td>
    </tr>
</table><!--/allrecords--></body>
</html>
