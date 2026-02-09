<table id="rec549847939" style="width:100%; border-collapse:collapse; border-spacing:0; margin:0; border:0;"
       cellpadding="0" cellspacing="0" data-record-type="619">
    <tr>
        <td style="padding-left:15px; padding-right:15px; ">
            <table id="recin549847939" class="r" style="margin: 0 auto;border-spacing: 0;width:600px;"
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
<table id="rec538399020" style="width:100%; border-collapse:collapse; border-spacing:0; margin:0; border:0;"
       cellpadding="0" cellspacing="0" data-record-type="627">
    <tr>
        <td style="padding-left:15px; padding-right:15px; ">
            <table id="recin538399020" class="r" style="margin: 0 auto;border-spacing: 0;width:600px;"
                   align="center">
                <tr>
                    <td style="padding-top:0px;padding-bottom:30px;padding-left:0;padding-right:0;">
                        <table border="0" cellpadding="0" cellspacing="0" width="100%">
                            <tr>
                                <td style="text-align: center;">
                                    <div
                                        style="margin: 0 auto; font-weight: normal; font-family: Helvetica Neue, Helvetica, Arial, sans-serif; color:#222222;font-size:13px;line-height:1.5;max-width:450px;">
                                        Вы получили это письмо, потому что зарегистрированы на портале
                                        <a href="{{url("/")}}" target="_blank" rel="noreferrer noopener">ASTT.SU</a>.
                                        Добавьте наш адрес {{config('mail.from.address')}} в Вашу адресную книгу, чтобы
                                        гарантированно получать от нас рассылки. Если данное письмо получено по ошибке,
                                        проигнорируйте его.
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <td style="text-align: center; padding-top: 25px;">
                                    <div
                                        style="margin: 0 auto; font-weight: normal; font-family: Helvetica Neue, Helvetica, Arial, sans-serif; color:#a1a1a1;font-size:12px;">
                                        Вы можете
                                        <a href="{{url("/unsubscribe?type=$key&unsubscribe_hash=$unsubscribeHash&email=".urlencode($email))}}">отписаться</a>
                                        от рассылки в любое время
                                    </div>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>
