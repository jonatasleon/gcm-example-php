function selecionar_tudo(){
    for (i=0;i<document.f1.elements.length;i++)
    if(document.f1.elements[i].type == "checkbox") document.f1.elements[i].checked=1
}
function deselecionar_tudo(){
    for (i=0;i<document.f1.elements.length;i++)
    if(document.f1.elements[i].type == "checkbox") document.f1.elements[i].checked=0
}
function desHabilita(id){
    if(document.getElementById("eventoRadio") == id){
        document.getElementById("linkRadio").checked = 0;
        document.getElementById("messageRadio").checked = 0;
        document.getElementById("message").disabled = true;
        document.getElementById("titulo").disabled = false;
        document.getElementById("desc").disabled = false;
        document.getElementById("local").disabled = false;
        document.getElementById("link").disabled = true;
    }else if(document.getElementById("messageRadio") == id){
        document.getElementById("linkRadio").checked = 0;
        document.getElementById("eventoRadio").checked = 0;
        document.getElementById("message").disabled = false;
        document.getElementById("titulo").disabled = true;
        document.getElementById("desc").disabled = true;
        document.getElementById("local").disabled = true;
        document.getElementById("link").disabled = true;
    }else if(document.getElementById("linkRadio") == id	){
        document.getElementById("messageRadio").checked = 0;
        document.getElementById("eventoRadio").checked = 0;
        document.getElementById("message").disabled = true;
        document.getElementById("titulo").disabled = true;
        document.getElementById("desc").disabled = true;
        document.getElementById("local").disabled = true;
        document.getElementById("link").disabled = false;
    }
} 
