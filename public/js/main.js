function mascaraCPF(i) {
    var v = i.value;
    if (isNaN(v[v.length - 1])) { // impede digitar outro caractere que não seja número
        i.value = v.substring(0, v.length - 1);
        return;
    }
    i.setAttribute("maxlength", "14");
    if (v.length == 3 || v.length == 7) i.value += ".";
    if (v.length == 11) i.value += "-";
}

function mascaraCEP(i) {
    var v = i.value;
    if (isNaN(v[v.length - 1])) { // impede digitar outro caractere que não seja número
        i.value = v.substring(0, v.length - 1);
        return;
    }
    i.setAttribute("maxlength", "9");
    if (v.length == 5) i.value += "-";
}

function mascaraCelular(i) {
    var v = i.value;
    if (isNaN(v[v.length - 1])) { // impede digitar outro caractere que não seja número
        i.value = v.substring(0, v.length - 1);
        return;
    }
    i.setAttribute("maxlength", "15");
    if (v.length == 1) i.value = "(" + v;
    if (v.length == 3) i.value += ") ";
    if (v.length == 10) i.value += "-";
}

function mascaraData(i) {
    var v = i.value;
    if (isNaN(v[v.length - 1])) { // impede digitar outro caractere que não seja número
        i.value = v.substring(0, v.length - 1);
        return;
    }
    i.setAttribute("maxlength", "10");
    if (v.length == 2 || v.length == 5) i.value += "/";
}


