$(document).ready(function() {

    //si modale existe -> l'afficher à l'ouverture de la page
    if($("#modale-modifications").length) {

        $("#modale-modifications").modal("show");
    }

    setTimeout(function() {

        $("#msg-panier-vide").hide();
    }, 5000);

    $("#msg-panier-vide").on("click", function() {

        $(this).hide();
    })

    $(".poubelle-reduite").on("click", function(event) {

        //l'evenement on click est declenche 2 fois pour un element place dans un span
        //il faut stopper la propagation de l'evenement
        event.stopPropagation();
        let id = $(this).data("id");
        $("#poubelle_" + id).trigger("click");
    })

    $(".span-poubelle").one("click", function() {

        $(this).children().first().trigger("click");
    })

    $(".poubelle").on("click", function(event) {

        //l'evenement on click est declenche 2 fois pour un element place dans un span
        //il faut stopper la propagation de l'evenement
        event.stopPropagation();
        loader(true);
        let id_produit = $(this).data("id");
        let quantite = $(this).data("quantite");
        let cible_hr = $(this).closest('.row').next();

        $.ajax({
            type: "POST",
            url: "/panier/retrait",
            data: {

                id_produit: id_produit,
                quantite: quantite,
            },
            success: function(response) {

                response = JSON.parse(response);

                if(response.nombre_articles < 1) {

                    location.reload();
                }

                else {

                    $("#compteur-panier").text(response.nombre_articles);
                    $("#prix_ht").text(formatteNombre(response.total_ht/100));
                    $("#prix_ttc").text(formatteNombre(response.total_ttc/100));
                    $("#prix_tva").text(formatteNombre(response.total_ttc/100 - response.total_ht/100));
                    cible_hr.remove();
                    $("#ligne_article_" + id_produit).remove();
                    $("#rang_reduit_" + id_produit).remove();
                    toastr.success("produit retiré du panier");
                    loader(false);

                    if(!$("#ligne-promo").hasClass("d-none")) {

                        $("#bouton-reset-promo").trigger("click");
                    }
                }                
            },
            error: function(err) {

                loader(false);
                //console.log(err);
                toastr.error("Erreur retrait produit du panier");
            }
        })
    })

    $('.quantite-reduite').on("change", function(event) {

        //l'evenement on click est declenche 2 fois pour un element place dans un span
        //il faut stopper la propagation de l'evenement
        event.stopPropagation();
        let cible = $(this).data("cible");
        $(cible).val($(this).val());
        $(cible).trigger("change");
    })

    $(".quantite").on("change", function() {

        loader(true);

        //controle en front qu'on n'entre pas une quantite superieure à ce qui present en stock
        //dans le cas ou le produit peut etre commande sans stock, limite a 99 geree dans la vue
        let entree = $(this).val();
        entree = parseInt(entree);
        let max = $(this).attr("max");
        max = parseInt(max);

        if(entree > max) {

            $(this).val($(this).attr("max"));
        }

        //la quantite en version reduite (sm et moins) est solidaire de la quantite en version taille md et plus
        let id_en_cours = $(this).attr("id");
        id_en_cours = id_en_cours.split("quantite_article_");
        let id_produit = id_en_cours[1];
        $("#quantite_reduite_" + id_produit).val($(this).val());

        if($(this).val() == 0) {

            $("#poubelle_" + id_produit).trigger("click");
        }

        else {

            $.ajax({
                type: "POST",
                url: "/panier/modifie-quantite",
                data: {
    
                    id_produit: id_produit,
                    quantite: $(this).val(),
                    mode: "edition"
                },
                success: function(response) {
    
                    response = JSON.parse(response);
    
                    if(response.nombre_articles != false) {
    
                        //securite: cas ou le user modifie le front pour entrer une quantite superieure au stock
                        //le back renvoie la quantite reelle
                        if($("#quantite_article_" + id_produit).val() > response.quantite_finale_produit) {
    
                            $("#quantite_article_" + id_produit).val(response.quantite_finale_produit);
                            $("#quantite_reduite_" + id_produit).val(response.quantite_finale_produit);
                        }
    
                        let prix_unitaire = convertionNombreTextePourCalcul($("#prix_unitaire_" + id_produit).text());
                        let total = $("#quantite_article_" + id_produit).val() * prix_unitaire;
                        total = convertionNombrePourAffichage(total);  
                        $("#prix_total_article_" + id_produit).text(total);
                        $("#prix_total_article_reduit_" + id_produit).text(total);
                        $("#compteur-panier").text(response.nombre_articles);
                        $("#prix_ht").text(formatteNombre(response.total_ht/100));
                        $("#prix_ttc").text(formatteNombre(response.total_ttc/100));
                        $("#prix_tva").text(formatteNombre(response.total_ttc/100 - response.total_ht/100));
                    }
    
                    else {
    
                        toastr.error("Erreur lors de la modification du panier");
                    }
    
                    loader(false);
    
                    if(!$("#ligne-promo").hasClass("d-none")) {
    
                        $("#bouton-code-promo").trigger("click");
                    }
                },
                error: function(err) {
    
                    loader(false);
                    //console.log(err);
                    toastr.error("Erreur ajout produit au panier");
                }
            })
        }
    })

    $("#bouton-code-promo").on("click", function() {

        loader(true);

        $.ajax({
            type: "POST",
            url: "/promo/recupere",
            data: {

                code: $("#input-code-promo").val()
            },
            success: function(response) {

                response = JSON.parse(response);

                $("#ligne-promo").removeClass("d-none");

                if(response.erreur != "") {

                    $("#description-promo").text(response.erreur);
                    $("#valeur-promo").text("");
                    toastr.error("Erreur code promo");
                }

                else {

                    $("#description-promo").text(response.description);
                    let reduction = response.reduction/100;
                    $("#valeur-promo").text("- €" + formatteNombre(reduction));
                    $("#tr-total-promo").removeClass("d-none");
                    $("#td-total-promo").text("- €" + formatteNombre(reduction));
                    let total_base = $("#prix_ttc").data("prix");
                    let nouveau_total = total_base - reduction;
                    nouveau_total = pseudoArrondi(nouveau_total);
                    $("#prix_ttc").text(nouveau_total);
                    toastr.success("Code promo appliqué");
                }

                loader(false);
            },
            error: function(err) {

                loader(false);
                //console.log(err);
                toastr.error("Erreur ajout produit au panier");
            }
        })
    })

    $("#bouton-reset-promo").on("click", function() {

        loader(true);

        $.ajax({

            type: "POST",
            url: "/promo/reset",
            success: function() {

                $("#tr-total-promo").addClass("d-none");
                $("#ligne-promo").addClass("d-none");
                let total_base = convertionNombreTextePourCalcul($("#prix_ttc").text());
                let reduction = $("#td-total-promo").text();
                reduction = reduction.split("- €");
                reduction = reduction[1];
                reduction = convertionNombreTextePourCalcul(reduction);
                total_base = total_base + reduction;
                total_base = pseudoArrondi(total_base);
                $("#prix_ttc").text(total_base);
                loader(false);
            },
            error: function() {

                loader(false);
                toastr.error("Erreur: la suppression du code promo a echoué");
            }
        })
    })

    $("#bouton-vide-panier").on("click", function() {

        console.log("vide panier");

        loader(true);

        $.ajax({

            type: "POST",
            url: "vide",
            success: function() {

                location.reload();
            },
            error: function() {

                loader(false);
                toastr.error("Erreur: le panier n'a pas pu etre vidé");
            }
        })
    })

    function pseudoArrondi(nombre) {

        nombre = Math.trunc(nombre * 100);
        nombre = nombre/100;
        return nombre;
    }

    function convertionNombreTextePourCalcul(texte) {

        texte = texte.replace(",", ".");
        texte = texte.replace("€", "");
        texte = parseFloat(texte);
        return texte;
    }

    function convertionNombrePourAffichage(prix) {

        prix = formatteNombre(prix);
        prix = prix.replace(".", ",");
        return prix;
    }

    function formatteNombre(nombre) {

        nombre = nombre.toFixed(2) + '';
        nombre = nombre.split(".");
        let entier = nombre[0];
        let decimale = nombre[1];
        let rgx = /(\d+)(\d{3})/;

        while (rgx.test(entier)) {
            entier = entier.replace(rgx, '$1' + ' ' + '$2');
        }

        return (entier + ',' + decimale);
    }

    function loader(show) {

        if(show) {

            $("#loader").show();
        }

        else {
            
            $("#loader").hide();
        }
    }
})