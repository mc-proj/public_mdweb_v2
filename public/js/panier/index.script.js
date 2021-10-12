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

    //
    /*$("#test").on("click", function() {
        test42();
    })

    function test42() {

        $("#ligne_article_" + 130).css("background-color", "pink");
    }*/
    //

    // console.log(test_app);

    $(".quantite").on("change", function() {

        loader(true);


        /*  normallmeent useless now
        //controle en front qu'on n'entre pas une quantite superieure à ce qui present en stock
        //dans le cas ou le produit peut etre commande sans stock, limite a 99 geree dans la vue
        let entree = $(this).val();
        entree = parseInt(entree);
        let max = $(this).attr("max");
        max = parseInt(max);

        if(entree > max) {

            $(this).val($(this).attr("max"));
        }*/

        //la quantite en version reduite (sm et moins) est solidaire de la quantite en version taille md et plus
        let id_en_cours = $(this).attr("id");
        id_en_cours = id_en_cours.split("quantite_article_");
        let id_produit = id_en_cours[1];
        let quantite_editee = $(this).val();
        $("#quantite_reduite_" + id_produit).val(quantite_editee);

        if(quantite_editee === 0) {
            $("#poubelle_" + id_produit).trigger("click");
        }

        else {

            $.ajax({
                type: "POST",
                //url: racine + "/paniers/modifie-quantite",
                url: "/paniers/modifie-quantite",
                data: {
    
                    id_produit: id_produit,
                    quantite: quantite_editee,
                    mode: "edition"
                },
                success: function(response) {
    
                    response = JSON.parse(response);

                    //console.log(response);
                    //Object { produit_dispo_sans_stock: false, quantite_produit_stock: 50, quantite_finale_produit: "2", nombre_articles_panier: 7, total_ht: 50102, total_ttc: 52856 }
                    //ajout response.edite_supprime (detection qte d'un article passe a 0 --> suppression de l'artcle)
                    // if edite supprime true && nb articles === 0 --> vidangeVisuellePanier();

                    //si edite supprime et nb articles > 0
                    //suppr ligne article a 0
                    // id="ligne_article_" + id article et <hr> qui le suis a suppr ?

                    /*
                    $("#ligne_article_130").next("hr").remove();
                    $("#ligne_article_130").remove();
                    */

                   /*
                    apres modif qte
                        verifier qte en session a ete changee  --> OK mis a jour en back
                        verifier attr des inputs (std et reduit) --> max

                                {% if produit.getCommandableSansStock() %}   ---- if response.produit_dispo_sans_stock
                                    max="99"
                                {% else %}
                                    max="{{ produit.getQuantiteStock() - quantite_panier }}"

                                    -->response.quantite_produit_stock - response.quantite_finale_produit
                                {% endif %}
                   */

                    //cas ou la quantite a été corrigée par les securites en back
                    if(response.quantite_finale_produit !== quantite_editee) {
                        $("#quantite_article_" + id_produit).val(response.quantite_finale_produit);
                        $("#quantite_reduite_" + id_produit).val(response.quantite_finale_produit);
                    }

                    if(!response.produit_dispo_sans_stock) {
                        $("#quantite_article_" + id_produit).attr("max", response.quantite_produit_stock - response.quantite_finale_produit);
                        $("#quantite_reduite_" + id_produit).attr("max", response.quantite_produit_stock - response.quantite_finale_produit);
                    }

                    //la quantite pour ce produit est de 0 --> suppression visuelle du panier
                    if(response.edite_supprime) {
                        if(response.nombre_articles_panier === 0) { //panier vide
                            vidangeVisuellePanier();
                        } else {
                            $("#ligne_article_" + id_produit).next("hr").remove();
                            $("#ligne_article_" + id_produit).remove();
                        }
                    } else {
                        let prix_unitaire = convertionNombreTextePourCalcul($("#prix_unitaire_" + id_produit).text());
                        let total = response.quantite_finale_produit * prix_unitaire; //qte article remplacee par celle renvoyee par ctrleur (secu)
                        total = CurrencyFormatted(total);
                        $("#prix_total_article_" + id_produit).text(total);
                        $("#prix_total_article_reduit_" + id_produit).text(total);
                    }

                    $("#compteur-panier").text(response.nombre_articles_panier);
                    $("#prix_ht").text(CurrencyFormatted(response.total_ht/100));
                    $("#prix_ttc").text(CurrencyFormatted(response.total_ttc/100));
                    $("#prix_tva").text(CurrencyFormatted(response.total_ttc/100 - response.total_ht/100));
                    
                    
                    
                    //if(response.nombre_articles != false) {  //secu useless ?
    
                        //securite: cas ou le user modifie le front pour entrer une quantite superieure au stock
                        //le back renvoie la quantite reelle
                        //now useless -- secus en back
                        /*if($("#quantite_article_" + id_produit).val() > response.quantite_finale_produit) {
    
                            $("#quantite_article_" + id_produit).val(response.quantite_finale_produit);
                            $("#quantite_reduite_" + id_produit).val(response.quantite_finale_produit);
                        }*/

                        //CurrencyFormatted
                        //produit 7 -> id 128
    
                        /*let prix_unitaire = convertionNombreTextePourCalcul($("#prix_unitaire_" + id_produit).text());
                        let total = response.quantite_finale_produit * prix_unitaire; //qte article remplacee par celle renvoyee par ctrleur (secu)
                        total = CurrencyFormatted(total);
                        $("#prix_total_article_" + id_produit).text(total);
                        $("#prix_total_article_reduit_" + id_produit).text(total);

                        $("#compteur-panier").text(response.nombre_articles_panier);
                        $("#prix_ht").text(CurrencyFormatted(response.total_ht/100));
                        $("#prix_ttc").text(CurrencyFormatted(response.total_ttc/100));
                        $("#prix_tva").text(CurrencyFormatted(response.total_ttc/100 - response.total_ht/100));*/
                    //}
    
                    /*else {
    
                        toastr.error("Erreur lors de la modification du panier");
                    }*/
    
                    loader(false);
    
                    if(!$("#ligne-promo").hasClass("d-none")) {
    
                        $("#bouton-code-promo").trigger("click");
                    }

                    //ori
                    /*if(response.nombre_articles != false) {
    
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
                    }*/
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

    function vidangeVisuellePanier() {
        let contenu = "<h1>Mon panier</h1><hr>";
        contenu += "<div class='row' id='ligne-panier-vide'>";
        contenu += "<div class='col-12' id='case-panier-vide'>";
        contenu += "Votre panier est actuellement vide";
        contenu += "</div></div>";
        contenu += "<a href='/produits' class='btn' id='bouton-retour-boutique'>RETOUR A LA BOUTIQUE</a>";
        $("#containeur-principal").empty();
        $("#containeur-principal").append(contenu);
    }

    function CurrencyFormatted(amount) {
        return amount.toLocaleString('fr-FR', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        });
    }

    //debut useless zone
    function pseudoArrondi(nombre) {

        nombre = Math.trunc(nombre * 100);
        nombre = nombre/100;
        return nombre;
    }

    function convertionNombreTextePourCalcul(texte) { //utile !!!

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
    //fin useless zone

    function loader(show) {
        if(show) {
            $("#loader").show();
        } else {
            $("#loader").hide();
        }
    }
})