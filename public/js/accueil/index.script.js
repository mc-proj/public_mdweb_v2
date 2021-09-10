$(document).ready(function() {

    let compteur_clignote = 0;
    let clignote = setInterval(function(){
        blink();
    }, 500);

    function blink() {

        $("#texte-accueil").animate({opacity:0},500,"linear").animate({opacity:1},500,"linear")
        compteur_clignote += 1;

        if(compteur_clignote == 3) {

            clearInterval(clignote);
        }
    }        
})