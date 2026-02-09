import type {IFormAnswer} from "~/types/formAnswer";

export const STANDART_QUESTION_TYPES = ['living', 'security'];
export const CHECKBOX_TYPES = [/*'living', 'security',*/ 'orv', 'checkbox'];

export const generateTitle = (selectedType: any, vehicleCount: number, questions: any[], formAnswers: IFormAnswer[]) => {
    const vehicle = selectedType && selectedType.title;
    if (!vehicle) return 'Название заявки';
    const form = formAnswers.filter((item: any) => {
        return item.value !== 'false' && item.value !== false && item.value !== undefined;
    }).reduce((acc: string, item: any) => {
        const q = questions.find((q: any) => q.id === item.form_question_id);
        if (!q) return acc;
        if (CHECKBOX_TYPES.includes(q.type)) {
            return acc + q.label.toLowerCase() + " ";
        }
        return acc + item.value + " ";
    }, '');
    return `${vehicle} ${form}` + (vehicleCount > 1 ? ` ${vehicleCount} шт` : '');
}

export const getCustomQuestions = (questions: any[]) => {
    return questions.filter((q: any) => !STANDART_QUESTION_TYPES.includes(q.type))
}

export const getLivingQuestion = (questions: any[]) => {
    return questions.find((q: any) => q.type === 'living');
}

export const getLivingAnswer = (formAnswers: IFormAnswer[], questions: any[]) => {
    const question = getLivingQuestion(questions);
    if (!question) return false;
    const answer = formAnswers.find((a: any) => a.form_question_id === question.id);
    return answer && answer.value;
}

export const getSecurityQuestion = (questions: any[]) => {
    return questions.find((q: any) => q.type === 'security');
}

export const getSecurityAnswer = (formAnswers: IFormAnswer[], questions: any[]) => {
    const question = getSecurityQuestion(questions);
    if (!question) return false;
    const answer = formAnswers.find((a: any) => a.form_question_id === question.id);
    return answer && answer.value;
}