$(document).ready(function() {

    //--------------
    $(".bouton-facture").on("click", function() {
        loader(true);  //stand by pr tests

        $.ajax({
            type: "POST",
            url: racine + "factures/mafacture",
            data: {
                facture_id: $(this).data("id")
            },
            success: function(data) {

                let resultats = JSON.parse(data);
                //let date_creation_facture = convertiDates(resultats.date_creation);
                //reset de la modale qui affiche une facture
                $("#modale-adresse-facturation").text('');
                $("#modale-adresse-livraison").text('');
                $("#tr-promo").remove();
                $(".ligne-produit").remove();
                $("#row-message").remove();
                let produits = resultats.produit;

                for(produit_en_cours of produits) {
                    let produit = produit_en_cours.produit;
                    //let tarif = produit.tarif;
                    let tarif = produit.tarif_actif;
                    /*let debut_promo= convertiDates(produit.date_debut_promo);
                    let fin_promo= convertiDates(produit.date_fin_promo);*/
                    let texte_promo = "";

                    if(produit.en_promo) {
                        texte_promo = " (tarif promotionnel)";
                    }

                    /*if(date_creation_facture >= debut_promo && date_creation_facture <= fin_promo) {
                        tarif = produit.tarif_promo;
                        texte_promo = " (tarif promotionnel)";
                    }*/

                    //<a href="{{ path('vue_produit', {'nom_produit': produit.nom()}) }}" class="lien-article">

                    let ajout_produit = "<tr class='ligne-produit'>";
                    /*let categories = produit.categories;
                    let categorie = categories[0];*/
                    //let lien = racine + "produits/filtre/" + categorie.nom;
                    let lien = racine + "produits/details/" + produit.nom;

                    /*if(categorie.sous_categories.length > 0) {
                        let sous_categories = categorie.sous_categories;
                        let sous_categorie = sous_categories[0];
                        lien += "/" + sous_categorie.nom;
                    }

            

                    lien += "/" + produit.nom;*/

                    //@TODO: soucis de tarifs
                    console.log(tarif);


                    ajout_produit +="<td><a class='lien-article' href='" + lien + "'>" + produit.nom + "</a> x " + produit_en_cours.quantite + "</td>"
                    ajout_produit += "<td>€" + CurrencyFormatted(tarif/100 * produit_en_cours.quantite) + texte_promo + "</td>";
                    ajout_produit += "</tr>";
                    $("#body-table-facture").prepend(ajout_produit);
                }

                if(resultats.code_promo !== null) {
                    let promo = resultats.code_promo;
                    let ajout_promo = "<tr id='tr-promo'>";
                    ajout_promo += "<td>Code promo : " + promo.code + "</td>";
                    //en bdd, le montant ttc ne prend pas les codes promo en compte, montant total oui
                    ajout_promo += "<td>€" + CurrencyFormatted((resultats.montant_total - resultats.montant_ttc)/100) + "</td>";
                    ajout_promo += "</tr>";
                    $(ajout_promo).insertAfter($("#modale-tva").parent());
                }

                $("#modale-prix-total-ht").text("€" + CurrencyFormatted(resultats.montant_ht/100));
                let tva = CurrencyFormatted((resultats.montant_ttc - resultats.montant_ht)/100);
                $("#modale-tva").text("€" + tva);
                $("#modale-prix-total").text("€" + CurrencyFormatted(resultats.montant_total/100));
                let user = resultats.user;
                let texte_adresse_user = user.nom + " " + user.prenom + "\n";
                texte_adresse_user += user.adresse + "\n";
                texte_adresse_user += user.code_postal + " " + user.ville + "\n";
                $("#modale-adresse-facturation").text(texte_adresse_user);

                if(resultats.adresseLivraison === null) {

                    $("#modale-adresse-livraison").text(texte_adresse_user);
                }

                else {
                    let livraison = resultats.adresseLivraison;
                    let texte_adresse_livraison = livraison.nom + " " + livraison.prenom + "\n";
                    texte_adresse_livraison += livraison.adresse + "\n";
                    texte_adresse_livraison += livraison.code_postal + " " + livraison.ville + "\n";
                    $("#modale-adresse-livraison").text(texte_adresse_livraison);
                }

                if(resultats.message != null) {
                    let bloc_message = "<div class='row' id='row-message'>"//debut du div row
                    bloc_message += "<div class='col-4'>";//debut 1er col
                    bloc_message += "<span class='texte-souligne'>message pour la livraison</span> :"; //classe pour le style
                    bloc_message += "</div>";//fin 1er col
                    bloc_message += "<div class='col-8'>";//debut 2eme col
                    bloc_message += resultats.message;
                    bloc_message += "</div>";//fin 2eme col
                    bloc_message += "</div>";//fin du div row
                    $("#row-adresses").append(bloc_message);
                }

                loader(false);
                $("#modale-facture").modal("show");
            },
            error: function(err) {
                loader(false);
                //console.log(err);
            }
        })
    })

    //------------
    /*$(".bouton-facture").on("click", function() {
        //loader(true);  //stand by pr tests

        $.ajax({
            type: "POST",
            url: racine + "factures/mafacture",
            data: {
                facture_id: $(this).data("id")
            },
            success: function(data) {

                let resultats = JSON.parse(data);
                let date_creation_facture = convertiDates(resultats.date_creation);
                //reset de la modale qui affiche une facture
                $("#modale-adresse-facturation").text('');
                $("#modale-adresse-livraison").text('');
                $("#tr-promo").remove();
                $(".ligne-produit").remove();
                $("#row-message").remove();
                let produits = resultats.produit;

                for(produit_en_cours of produits) {
                    let produit = produit_en_cours.produit;
                    let tarif = produit.tarif;
                    let debut_promo= convertiDates(produit.date_debut_promo);
                    let fin_promo= convertiDates(produit.date_fin_promo);
                    let texte_promo = "";

                    if(date_creation_facture >= debut_promo && date_creation_facture <= fin_promo) {
                        tarif = produit.tarif_promo;
                        texte_promo = " (tarif promotionnel)";
                    }

                    let ajout_produit = "<tr class='ligne-produit'>";
                    let categories = produit.categories;
                    let categorie = categories[0];
                    let lien = racine + "produits/filtre/" + categorie.nom;

                    if(categorie.sous_categories.length > 0) {
                        let sous_categories = categorie.sous_categories;
                        let sous_categorie = sous_categories[0];
                        lien += "/" + sous_categorie.nom;
                    }

                    lien += "/" + produit.nom;
                    ajout_produit +="<td><a class='lien-article' href='" + lien + "'>" + produit.nom + "</a> x " + produit_en_cours.quantite + "</td>"
                    ajout_produit += "<td>€" + CurrencyFormatted(tarif/100 * produit_en_cours.quantite) + texte_promo + "</td>";
                    ajout_produit += "</tr>";
                    $("#body-table-facture").prepend(ajout_produit);
                }

                if(resultats.code_promo !== null) {
                    let promo = resultats.code_promo;
                    let ajout_promo = "<tr id='tr-promo'>";
                    ajout_promo += "<td>Code promo : " + promo.code + "</td>";
                    //en bdd, le montant ttc ne prend pas les codes promo en compte, montant total oui
                    ajout_promo += "<td>€" + CurrencyFormatted((resultats.montant_total - resultats.montant_ttc)/100) + "</td>";
                    ajout_promo += "</tr>";
                    $(ajout_promo).insertAfter($("#modale-tva").parent());
                }

                $("#modale-prix-total-ht").text("€" + CurrencyFormatted(resultats.montant_ht/100));
                let tva = CurrencyFormatted((resultats.montant_ttc - resultats.montant_ht)/100);
                $("#modale-tva").text("€" + tva);
                $("#modale-prix-total").text("€" + CurrencyFormatted(resultats.montant_total/100));
                let user = resultats.user;
                let texte_adresse_user = user.nom + " " + user.prenom + "\n";
                texte_adresse_user += user.adresse + "\n";
                texte_adresse_user += user.code_postal + " " + user.ville + "\n";
                $("#modale-adresse-facturation").text(texte_adresse_user);

                if(resultats.adresseLivraison === null) {

                    $("#modale-adresse-livraison").text(texte_adresse_user);
                }

                else {
                    let livraison = resultats.adresseLivraison;
                    let texte_adresse_livraison = livraison.nom + " " + livraison.prenom + "\n";
                    texte_adresse_livraison += livraison.adresse + "\n";
                    texte_adresse_livraison += livraison.code_postal + " " + livraison.ville + "\n";
                    $("#modale-adresse-livraison").text(texte_adresse_livraison);
                }

                if(resultats.message != null) {
                    let bloc_message = "<div class='row' id='row-message'>"//debut du div row
                    bloc_message += "<div class='col-4'>";//debut 1er col
                    bloc_message += "<span class='texte-souligne'>message pour la livraison</span> :"; //classe pour le style
                    bloc_message += "</div>";//fin 1er col
                    bloc_message += "<div class='col-8'>";//debut 2eme col
                    bloc_message += resultats.message;
                    bloc_message += "</div>";//fin 2eme col
                    bloc_message += "</div>";//fin du div row
                    $("#row-adresses").append(bloc_message);
                }

                loader(false);
                $("#modale-facture").modal("show");
            },
            error: function(err) {
                loader(false);
                //console.log(err);
            }
        })
    })*/

    /*function convertiDates(date) {
        let date_js = new Date(date).getTime();
        return date_js.toLocaleString('fr-FR');
    }*/

    function CurrencyFormatted(amount) {
        return amount.toLocaleString('fr-FR', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        });
    }

    function loader(show) {
        if(show) {
            $("#loader").show();
        }else {
            $("#loader").hide();
        }
    }
});