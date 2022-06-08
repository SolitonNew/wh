let langData = null;

export const setLangData = (data) => {
    langData = data;
}

export const lang = (text) => {
    if (langData && langData[text] !== undefined) {
        return langData[text];
    }
    return text;
}