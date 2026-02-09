import moment from 'moment/moment';
import 'moment/locale/ru';

export const ruMoment = (str?: moment.MomentInput) => {
    moment.locale('ru');
    return moment(str);
}