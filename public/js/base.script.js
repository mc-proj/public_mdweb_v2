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
        }else {
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
            url: route_apercu_panier,
            method: 'POST',
            success: function (response) {                
                let result = JSON.parse(response);
                let total = 0;
                let html = "";

                //le panier comporte au moins 1 article
                if(result.length > 0) {
                    for(produit of result) {
                        let prix = Math.round(produit.tarif.ttc) * produit.quantite;
                        total += prix;
                        prix = CurrencyFormatted(prix/100);
                        let image = "<img src='" + racine + "images/produits/" + produit.image + "' class='image-appercu' alt='image " + produit.nom + "'>";
                        html += "<div class='row' style='width: 100%'>";
                        html += "<div class='col-3 centre-verticallement'>" + image + "</div>";
                        html += "<div class='col-6'>";
                        html += "<u>Nom</u> : " + produit.nom + "<br><u>Quantit??</u> : " + produit.quantite + "<br>??? " + prix + "</div>";
                        html += "<div class='col-3 centre-verticallement'>";
                        html += "<button class='btn btn-secondary apercu-supprime' data-id='"+ produit.id +"' data-quantite='" + produit.quantite + "'>";
                        html += "<i class='fas fa-trash-alt'></i>";
                        html += "</button>"
                        html += "</div>"; //fin col-3
                        html += "</div><hr class='trait-hr'>"; //fin row
                    }
    
                    html += "<div class='row' style='width: 100%'>";
                    html += "<div class='col-8' id='bloc-total-gauche'>";
                    html += "<span id='span-total-panier'>Total Panier</span><span>Hors frais de livraison</span>"
                    html += "</div>"; //fin col-8
                    html += "<div class='col-4' id='bloc-total-droit'>??? " + CurrencyFormatted(total/100) + "</div>";
                    html += "<div class='col-12' id='bloc-bouton-apercu'><a class='btn btn-info' id='lien-apercu-panier' href='" + route_panier + "'>Voir mon Panier</a></div>";
                    html += "</div>"; //fin row
                } else {
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
                mode: "suppression",
            },
            success: function(response) {
                if(current_route == "accueil_panier") {
                    //si on se trouve sur la page panier, on provoque un f5
                    location.reload();
                } else {
                    let result = JSON.parse(response);
                    $("#compteur-panier").text(result.nombre_articles_panier);
                    //retrait de l'article de l'apercu
                    _this.parent().parent().next().remove();
                    _this.parent().parent().remove();

                    //cas panier vide
                    if(result.nombre_articles_panier == 0) {
                        let html = "<div class='row'>";
                        html += "<div class='col-12'>Votre panier est vide</div>";
                        html += "</div>";
                        $("#apercu-popover").html(html);
                    } else {
                        //correction du total affiche apres suppression;
                        $("#bloc-total-droit").text("???" + CurrencyFormatted(result.total_ttc/100));
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
                //console.log(err);
            }
        })
    })

    //gestion animation sablier
    let etape = 1;
    let spin = null;

    function spinHourglass(anime) {
        if(!anime) {
            clearInterval(spin);
            $("#sablier").css("visibility", "hidden");
            $("#sablier").css("rotate", "0deg");
            $("#sablier").attr("class", "fas fa-hourglass-start");
            etape = 1;
        } else {
            $("#sablier").css("visibility", "visible");
            spin = setInterval(function() {
                let classe = ""
                switch(etape) {
                    case 1:
                        $("#sablier").css("rotate", "0deg");
                        classe = "fas fa-hourglass-half";
                        $("#sablier").attr("class", "");
                        break;

                    case 2:
                        classe="fas fa-hourglass-end";
                        $("#sablier").attr("class", "");
                        break;

                    case 3:
                        $("#sablier").css("rotate", "90deg");
                        break;

                    case 4:
                        $("#sablier").css("rotate", "180deg");
                        etape = 0;
                        break;
                }

                if($("#sablier").attr("class") === "") {
                    $("#sablier").attr("class", classe);
                }

                etape++;
            }, 400)
        }
    }

    //gestion recherche dynamique
    let tempo = null;

    $("#texte-recherche").on("keyup", function() {
        if(tempo != null) {
            clearTimeout(tempo);
            tempo = null;
            spinHourglass(false);
        }

        let debut = $(this).val();
        tempo = setTimeout(function() {
            spinHourglass(true);
            $.ajax({
                type: "POST",
                url: racine + "commun/recherche",
                data: {
                    debut: debut
                },
                success: function(response) {
                    let results = JSON.parse(response);
                    $("#resultats-recherche").empty();
                    spinHourglass(false);

                    if(results.length == 0) {
                        $("#resultats-recherche").slideUp();
                    } else {
                        for(result of results) {
                            let lien = "<a href='" + racine + "produits/filtre/";
                            let categorie = result.categorie !== null ? result.categorie : "";
                            let sous_categorie = result.sous_categorie !== null ? result.sous_categorie : "";
                            let produit = typeof(result.nom_produit) !== "undefined" ? result.nom_produit : "";
                            let texte_lien = categorie;
                            lien += categorie;

                            if(sous_categorie !== "") {
                                texte_lien = sous_categorie;
                                lien += "/" + sous_categorie;
                            }

                            if(produit !== "") {
                                texte_lien = produit;
                                lien += "/" + produit;
                            }

                            lien += "'>" + texte_lien + "</a><br>";
                            $("#resultats-recherche").append(lien);
                        }

                        $("#resultats-recherche").slideDown();
                    }    
                },
                error: function(err) {
                    //console.log(err);
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

    function CurrencyFormatted(amount) {
        return amount.toLocaleString('fr-FR', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        });
    }
})