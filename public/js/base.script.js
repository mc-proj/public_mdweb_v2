$(document).ready(function() {

    //init popover
    $(function () {
        $('[data-toggle="popover"]').popover()
    })

    //apparition-disparition du formulaire de recherche
    //au clic sur l'icone "loupe"
    $("#icone-recherche").on("click", function() {

        $(".ligne-recherche").slideToggle();
    })

    //disparition du formulaire de recherche
    //au clic sur la croix de fermeture du formulaire
    $("#croix-recherche").on("click", function() {

        $("#icone-recherche").trigger("click");
    })

    //les listes deroulantes du menu de navigation se deroulent au survol
    $(".liste").hover(function() {

        $(this).find('[data-toggle=dropdown]').dropdown('toggle');
    })

    $("#bouton-fermeture-lateral").on("click", function() {

        if($("#menu-lateral").is(":visible")) {

            $("#cache").css("display", "none");
        }

        else {

            $("#cache").css("display", "block");
        }

        $("#menu-lateral").animate({'width': 'toggle'}, 300);
    });

    $("#burger-button").on("click", function() {

        $("#bouton-fermeture-lateral").trigger("click"); 
    })

    $(".sous-liste-normale").on("mouseover", function() {

         //ajout pseudo element pour avoir la barre rouge au dessus du texte
        $(this).parent().prev().addClass("special");
        $(this).parent().prev().css("color", "rgb(202, 64, 64)");
    })

    $(".sous-liste-normale").on("mouseleave", function() {

        //retrait pseudo element pour avoir la barre rouge au dessus du texte
       $(this).parent().prev().removeClass("special");
       $(this).parent().prev().css("color", "black");
   })

   $(".sous-liste-laterale").on("mouseover", function() {

        //ajout pseudo element pour avoir la barre rouge au dessus du texte
        $(this).parent().prev().addClass("special");
        $(this).parent().prev().css("color", "rgb(202, 64, 64)");
    })

    $(".sous-liste-laterale").on("mouseleave", function() {

    //retrait pseudo element pour avoir la barre rouge au dessus du texte
        $(this).parent().prev().removeClass("special");
        $(this).parent().prev().css("color", "black");
    })

    $("#logo-panier").popover({
        trigger: 'manual',
        placement: 'bottom',
        html: true,
    }).on("show.bs.popover", function() {
        $.ajax({
            url: racine + 'panier/apercu_panier',
            method: 'POST',
            success: function (response) {

                let result = JSON.parse(response);
                let total = 0;
                let html = "";

                //le panier comporte au moins 1 article
                if(result.length > 0) {

                    for(produit of result) {

                        let prix = produit.tarif * produit.quantite;
                        total += prix;
                        prix = formatteNombrePourAffichage(prix);
                        let image = "<img src='" + racine + "images/produits/" + produit.image + "' class='image-appercu' alt='image " + produit.nom + "'>";
    
                        html += "<div class='row'>";
                        html += "<div class='col-3 centre-verticallement'>" + image + "</div>";
                        html += "<div class='col-6'>";
                        html += "Nom: " + produit.nom + "<br>Quantité: " + produit.quantite + "<br>€" + prix + "</div>";
                        html += "<div class='col-3 centre-verticallement'>";
                        html += "<button class='btn btn-secondary apercu-supprime' data-id='"+ produit.id +"' data-quantite='" + produit.quantite + "'>";
                        html += "<i class='fas fa-trash-alt'></i>";
                        html += "</button>"
                        html += "</div>"; //fin col-3
                        html += "</div><hr class='trait-hr'>"; //fin row
                    }
    
                    html += "<div class='row'>";
                    html += "<div class='col-8' id='bloc-total-gauche'>";
                    html += "<span id='span-total-panier'>Total Panier</span><span>Hors frais de livraison</span>"
                    html += "</div>"; //fin col-8
                    html += "<div class='col-4' id='bloc-total-droit'>€" + formatteNombrePourAffichage(total) + "</div>";
                    html += "<div class='col-12' id='bloc-bouton-apercu'><a class='btn btn-info' id='lien-apercu-panier' href='" + route_panier + "'>Voir mon Panier</a></div>";
                    html += "</div>"; //fin row
                }

                else {

                    //panier vide
                    html += "<div class='row'>";
                    html += "<div class='col-12'>Votre panier est vide</div>";
                    html += "</div>";
                }

                $("#apercu-popover").html(html);
            }
        });
    }).on("mouseenter", function() {

        let _this = this;
        $(this).popover("show");
        $(".popover").on("mouseleave", function() {

            $(_this).popover("hide");
        })
    }).on("mouseleave", function() {

        let _this = this;
        setTimeout(function () {
            if(!$(".popover:hover").length) {

                $(_this).popover("hide");
            }
        }, 300);
    })

    //clic bouton suppr apercu panier
    $("body").on("click", ".apercu-supprime", function() {

        let _this = $(this);
        let id = _this.data("id");
        let quantite = _this.data("quantite");
        _this.parent().append("<div class='loader-row'></div>");

        $.ajax({
            type: "POST",
            url: route_retrait,
            data: {
                id_produit: id,
                quantite: quantite
            },
            success: function(response) {

                if(current_route == "panier") {

                    //si on se trouve sur la page panier, on provoque un f5
                    location.reload();
                }

                else {

                    let result = JSON.parse(response);
                    $("#compteur-panier").text(result.nombre_articles);
                    //retrait de l'article de l'apercu
                    _this.parent().parent().next().remove();
                    _this.parent().parent().remove();

                    //cas panier vide
                    if(result.nombre_articles == 0) {

                        let html = "<div class='row'>";
                        html += "<div class='col-12'>Votre panier est vide</div>";
                        html += "</div>";
                        $("#apercu-popover").html(html);
                    }

                    else {

                        //correction du total affiche apres suppression;
                        $("#bloc-total-droit").text("€" + formatteNombrePourAffichage(result.total_ttc));
                    }
                }
            },
            error: function(err) {

                //console.log(err);
                $(document).find(".loader-row").first().remove();
            }
        })

    })

    $("#bouton-cookies").on("click", function() {

        $.ajax({
            type: "POST",
            url: racine + "commun/cookies_acceptes",
            success: function() {

                $("#container-cookies").css("display", "none");
            },
            error: function(err) {

                console.log(err);
            }
        })
    })

    //gestion recherche dynamique
    let tempo = null;

    $("#texte-recherche").on("keyup", function() {

        if(tempo != null) {

            clearTimeout(tempo);
            tempo = null;
        }

        let debut = $(this).val();

        tempo = setTimeout(function() {

            $.ajax({
                type: "POST",
                url: "/recherche",
                data: {
                    debut: debut
                },
                success: function(response) {

                    let results = JSON.parse(response);
                    console.log(results);
                    $("#resultats-recherche").empty();

                    if(results.length == 0) {

                        $("#resultats-recherche").slideUp();
                    }

                    else {

                        for(result of results) {
                            
                            let lien = "<a href='/boutique/";

                            if(typeof result.nom_produit === "undefined") {

                                if(result.nom_categorie_parent === null) {

                                    lien = lien + result.nom_categorie + "'>" + result.nom_categorie + "</a><br>";
                                }

                                else {

                                    lien  = lien + result.nom_categorie_parent + "/" + result.nom_categorie + "'>" + result.nom_categorie + "</a><br>";
                                }
                            }

                            else {

                                lien = lien + result.nom_categorie;

                                if(result.nom_sous_categorie !== null) {

                                    lien = lien + "/" + result.nom_sous_categorie;
                                }

                                lien = lien + "/" + result.nom_produit + "'>" + result.nom_produit + "</a><br>";
                            }

                            $("#resultats-recherche").append(lien);
                        }

                        $("#resultats-recherche").slideDown();
                    }    
                },
                error: function(err) {

                    console.log(err);
                }
            })
        }, 700);
    })

    $("#bouton-cookies").on("click", function() {

        $("#container-cookies").css("display", "none");
    })

    $(".lien-liste").on("click", function() {

        window.location.replace($(this).attr("href"));
    })

    function formatteNombrePourAffichage(nombre) {

        nombre /= 100;
        nombre = nombre.toLocaleString('fr');

        //separation parties entiere et decimale
        let secu = nombre.split(",");
        //cas decimale == 0
        if(secu.length == 1) {

            nombre = nombre + ",00";
        }

        //cas decimale a 1 chiffre, ajout d'un zero : 12,3 => 12,30
        else if(secu[1].length == 1) {

            nombre += "0";
        }

        //si la decimale est sur plus de deux chiffres, on ne garde que les 2 premiers
        //12,3456 => 12,34
        else if(secu[1].length > 2) {

            let decimale = secu[1];
            nombre = secu[0] + "," + decimale[0] + decimale[1];
        }

        return nombre;
    }
})