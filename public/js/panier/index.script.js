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
        let cible_hr = $(this).closest('.row').next();
        let cible_message = cible_hr.closest('.row');

        $.ajax({
            type: "POST",
            url: racine + "paniers/modifie-quantite",
            data: {
                id_produit: id_produit,
                mode: "suppression"
            },
            success: function(response) {
                response = JSON.parse(response);

                if(typeof(response.erreur) !== "undefined") { //cas ou le user est un bricoleur
                    toastr.error(response.erreur);
                } else {
                    if(response.nombre_articles_panier < 1) {
                        location.reload();
                    } else {
                        $("#compteur-panier").text(response.nombre_articles_panier);
                        $("#prix_ht").text(CurrencyFormatted(response.total_ht/100));
                        $("#prix_ttc").text(CurrencyFormatted(response.total_ttc/100));
                        $("#prix_tva").text(CurrencyFormatted(response.total_ttc/100 - response.total_ht/100));
    
                        if(cible_message.hasClass('message-quantite')) {
                            //si le produit supprime a un message "quantite editee", on supprime aussi le msg d'alert en debut de page
                            let est_message_edition = cible_message.children().eq(0).text();
                            est_message_edition = est_message_edition.replace(/\s/g, ''); //supprime espaces
                
                            if(est_message_edition === "quantitéeditée") {
                                $(".alert-info").remove();
                            }
    
                            cible_message.next().remove();
                            cible_message.remove();
                        } else {
                            cible_hr.remove();
                        }
    
                        $("#ligne_article_" + id_produit).remove();
                        $("#rang_reduit_" + id_produit).remove();
                        toastr.success("produit retiré du panier");
                        secuPromo(response.infos_promo);
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
        //la quantite en version reduite (sm et moins) est solidaire de la quantite en version taille md et plus
        let id_en_cours = $(this).attr("id");
        id_en_cours = id_en_cours.split("quantite_article_");
        let id_produit = id_en_cours[1];
        let quantite_editee = $(this).val();
        $("#quantite_reduite_" + id_produit).val(quantite_editee);

        if(quantite_editee === 0) {
            $("#poubelle_" + id_produit).trigger("click");
        } else {
            $.ajax({
                type: "POST",
                url: racine + "paniers/modifie-quantite",
                data: {
                    id_produit: id_produit,
                    quantite: quantite_editee,
                    mode: "edition"
                },
                success: function(response) {
                    response = JSON.parse(response);

                    if(typeof(response.erreur) !== "undefined") { //cas ou le user est un bricoleur
                        toastr.error(response.erreur);
                    } else {
                        //cas ou la quantite a été corrigée par les securites en back
                        if(response.quantite_finale_produit !== quantite_editee) {
                            $("#quantite_article_" + id_produit).val(response.quantite_finale_produit);
                            $("#quantite_reduite_" + id_produit).val(response.quantite_finale_produit);
                        }

                        if(!response.produit_dispo_sans_stock) {
                            $("#quantite_article_" + id_produit).attr("max", response.quantite_produit_stock - response.quantite_finale_produit);
                            $("#quantite_reduite_" + id_produit).attr("max", response.quantite_produit_stock - response.quantite_finale_produit);
                        } else  {
                            $(".stock_" + id_produit).remove();

                            if(response.quantite_finale_produit > response.quantite_produit_stock) {
                                let message_quantite = "<div class='row message-quantite stock_" + id_produit + "'>";
                                message_quantite += "<div class='col-12'>";
                                message_quantite += response.quantite_produit_stock + " sont disponibles en stock<br>";
                                message_quantite += (response.quantite_finale_produit - response.quantite_produit_stock) + " seront livrés ultérieurement";
                                message_quantite += "</div></div>";
                                $("#ligne_article_" + id_produit).after(message_quantite);
                                $("#rang_reduit_" + id_produit).after(message_quantite);
                            }
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
                        secuPromo(response.infos_promo);
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
            url: racine + "paniers/promo",
            data: {
                code: $("#input-code-promo").val()
            },
            success: function(response) {

                response = JSON.parse(response);

                $("#ligne-promo").removeClass("d-none");
                let base_ht = convertionNombreTextePourCalcul($("#prix_ht").text());
                let tva = convertionNombreTextePourCalcul($("#prix_tva").text());

                if(typeof(response.erreur) !== "undefined") {
                    $("#description-promo").text(response.erreur);
                    $("#valeur-promo").text("");
                    $("#tr-total-promo").addClass("d-none");
                    $("#prix_ttc").text(CurrencyFormatted(base_ht + tva));
                    toastr.error("Erreur code promo");
                } else {
                    $("#description-promo").text(response.description);
                    let reduction = response.reduction/100;
                    $("#valeur-promo").text("- €" + CurrencyFormatted(reduction));
                    $("#tr-total-promo").removeClass("d-none");
                    $("#td-total-promo").text("- €" + CurrencyFormatted(reduction));
                    let total_base = base_ht + tva;
                    $("#prix_ttc").text(CurrencyFormatted(total_base - reduction));
                    toastr.success("Code promo appliqué");
                }

                loader(false);
            },
            error: function(err) {
                loader(false);
                //console.log(err);
                toastr.error("Erreur ajout code promo");
            }
        })
    })

    $("#bouton-reset-promo").on("click", function() {
        loader(true);

        $.ajax({

            type: "POST",
            url: racine + "paniers/reset_promo",
            success: function() {
                $("#tr-total-promo").addClass("d-none");
                $("#ligne-promo").addClass("d-none");
                $("#input-code-promo").val("");
                let base_ht = convertionNombreTextePourCalcul($("#prix_ht").text());
                let tva = convertionNombreTextePourCalcul($("#prix_tva").text());
                $("#prix_ttc").text(CurrencyFormatted(base_ht + tva));
                loader(false);
            },
            error: function() {
                loader(false);
                toastr.error("Erreur: la suppression du code promo a echoué");
            }
        })
    })

    $("#bouton-vide-panier").on("click", function() {
        loader(true);

        $.ajax({
            type: "POST",
            url: racine + "paniers/vide_panier",
            success: function() {
                location.reload();
            },
            error: function() {
                loader(false);
                toastr.error("Erreur: le panier n'a pas pu etre vidé");
            }
        })
    })

    function secuPromo(infos) {
        let erreur = infos["erreur"];
        let base_ht = convertionNombreTextePourCalcul($("#prix_ht").text());
        let tva = convertionNombreTextePourCalcul($("#prix_tva").text());
        if(erreur !== "" && erreur !== "nocode") {
            $("#description-promo").text(erreur);
            $("#valeur-promo").text("");
            $("#tr-total-promo").addClass("d-none");
            $("#prix_ttc").text(CurrencyFormatted(base_ht + tva));
        } else if(erreur === "") {
            let reduction = infos["reduction"];
            $("#prix_ttc").text(CurrencyFormatted(base_ht + tva - reduction/100));
        }
        loader(false);
    }

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
    /*function pseudoArrondi(nombre) {

        nombre = Math.trunc(nombre * 100);
        nombre = nombre/100;
        return nombre;
    }*/

    function convertionNombreTextePourCalcul(texte) { //utile !!!

        texte = texte.replace(",", ".");
        texte = texte.replace("€", "");
        texte = parseFloat(texte);
        return texte;
    }

    /*function convertionNombrePourAffichage(prix) {

        prix = formatteNombre(prix);
        prix = prix.replace(".", ",");
        return prix;
    }*/

    /*function formatteNombre(nombre) {

        nombre = nombre.toFixed(2) + '';
        nombre = nombre.split(".");
        let entier = nombre[0];
        let decimale = nombre[1];
        let rgx = /(\d+)(\d{3})/;

        while (rgx.test(entier)) {
            entier = entier.replace(rgx, '$1' + ' ' + '$2');
        }

        return (entier + ',' + decimale);
    }*/
    //fin useless zone

    function loader(show) {
        if(show) {
            $("#loader").show();
        } else {
            $("#loader").hide();
        }
    }
})