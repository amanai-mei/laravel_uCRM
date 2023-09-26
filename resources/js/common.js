// 改行
const nl2br = (str) => {
    var res = str.replace(/\r\n/g, "<br>");
    res = res.replace(/(\n|\r)/g, "<br>");
    return res;
}

// 当日の日付
const getToday = () => {
    const today = new Date(); //new Dateで日付の情報を取得
    const yyyy = today.getFullYear(); //年の情報を取得
    //monthは0からスタートになるので＋1をする
    const mm = ("0"+(today.getMonth()+1)).slice(-2); //月の情報を取得
    // 月日に関しては2桁表示にしたいので一桁の場合は0をつけるようにする
    const dd = ("0"+today.getDate()).slice(-2); //日の情報を取得
    return yyyy+'-'+mm+'-'+dd; //最終的に表示させたいもの
}


export { nl2br,getToday } //別のファイルでもこの関数が使えるようになる
