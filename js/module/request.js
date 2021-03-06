import { deactivateButton, singleError, multiError, getFatalError, sendVerifiedEmail } from "./dom.js";


// RICHIESTA FILE DI JSON --------------------------------------------------
/**
 *
 * @param {string} file - file php al quale fare la request
 * @param {string} data - stringa json da far processare lato backend
 *
 * readyState values:
 * 0: request not initialized (.onerror)
 * 1: server connection established
 * 2: request received
 * 3: processing request (.onprogress)
 * 4: request finished and response is ready (.onload)
 *
 * HTTP Status
 * 200: "OK"
 * 403: "Forbidden"
 * 404: "Not Found"
 */
function request(file, data=null){

    return new Promise((resolve, reject) =>{
// var xhr = typeof XMLHttpRequest != 'undefined' ? new XMLHttpRequest() : new ActiveXObject('Microsoft.XMLHTTP');
    const xhttp = new XMLHttpRequest();

    xhttp.onreadystatechange = function() {  //console.log("readyState: "+xhttp.readyState);

        switch ( this.readyState ) {

            case 0: console.log("readyState 0: "+xhttp.readyState); break;

            case 1: console.log("readyState 1: "+xhttp.readyState);

                deactivateButton(file);
                break;

            case 2: console.log("readyState 2: "+xhttp.readyState); break;

            case 3: console.log("readyState 3: "+xhttp.readyState); break;

            case 4: console.log("readyState 4: "+xhttp.readyState);

                if (this.status >= 200 && this.status < 300) {

                    let str = xhttp.responseText;  console.log(str);

                    let obj = JSON.parse(str);   console.log(obj);
/*
                    if ( Array.isArray(obj) ) { // Se è un array di oggetti

                        if (obj.some(e => e.status === 'error')) { console.log("OK");

                            multiError( obj );
                        }
                    } else {  console.log("KO");
*/
                        if(obj.status === "success"){

                            resolve(obj.message);

                        } else if(obj.status === "error"){

                            switch (parseInt(obj.code, 10)){

                            case -30: getFatalError(obj, 'contattare lo sviluppatore'); break;
                            case -20: getFatalError(obj, 'contattare lo sviluppatore'); break;
                            case -10:
                                switch (obj.type){
                                    case "verified": sendVerifiedEmail(obj); break;
                                    case "email": singleError(obj); break;
                                    case "bug": reject("BUG: "+obj.bug ); break;
                                    case "test": reject("TEST: "+obj.message ); break;
                                    default: singleError(obj);
                                }
                            break;
                            }
                        } else if(obj[0].status === "errors"){

                            multiError(obj);
                        }
                   // }
                } else {
                    //activateButton(obj.page);
                    reject("Errore code:"+this.status);
                }
        break;
        }
    };
    xhttp.open("POST", "src/controller/"+file+".php", true);
    xhttp.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
    xhttp.send(data);
    });
}


export { request };
