$(document).ready(function() {

    $(".bouton-facture").on("click", function() {

        loader(true);

        $.ajax({

            type: "POST",
            url: "factures/unefacture",
            data: {
                facture_id: $(this).data("id")
            },
            success: function(data) {

                let resultats = JSON.parse(data);
                let date_creation_facture = convertiDates(resultats.date_creation);
                /*on enleve les donnees de la precedente facture affichee*/
                $("#modale-adresse-facturation").text('');
                $("#modale-adresse-livraison").text('');
                $("#tr-promo").remove();
                $(".ligne-produit").remove();
                $("#row-message").remove();

                for(produit of resultats.produits) {

                    let tarif = produit.produit.tarif;
                    let debut_promo= convertiDates(produit.produit.date_debut_promo);
                    let fin_promo= convertiDates(produit.produit.date_fin_promo);
                    let texte_promo = "";

                    if(date_creation_facture >= debut_promo && date_creation_facture <= fin_promo) {

                        tarif = produit.produit.tarif_promo;
                        texte_promo = " (tarif promotionnel)";
                    }

                    let ajout_produit = "<tr class='ligne-produit'>";
                    let categories = produit.produit.categories;
                    let lien = "/boutique/" + categories[0];

                    if(categories[1] != null) {

                        lien += "/" + categories[1];
                    }

                    lien += "/" + produit.produit.nom;
                    ajout_produit +="<td><a href='" + lien + "'>" + produit.produit.nom + "</a> x " + produit.quantite + "</td>"
                    ajout_produit += "<td>€" + formatteNombrePourAffichage(tarif * produit.quantite) + texte_promo + "</td>";
                    ajout_produit += "</tr>";
                    $("#body-table-facture").prepend(ajout_produit);

                }

                if(resultats.code_promo != null) {

                    let promo = resultats.code_promo;
                    let ajout_promo = "<tr id='tr-promo'>";
                    ajout_promo += "<td>Code promo : " + promo.code + "</td>";
                    //en bdd, le montant ttc ne prend pas les codes promo en compte, montant total oui
                    ajout_promo += "<td>€" + formatteNombrePourAffichage(resultats.montant_total - resultats.montant_ttc) + "</td>";
                    ajout_promo += "</tr>";
                    $(ajout_promo).insertAfter($("#modale-tva").parent());
                }

                $("#modale-prix-total-ht").text("€" + formatteNombrePourAffichage(resultats.montant_ht));
                let tva = formatteNombrePourAffichage(resultats.montant_ttc - resultats.montant_ht);
                $("#modale-tva").text("€" + tva);
                $("#modale-prix-total").text("€" + formatteNombrePourAffichage(resultats.montant_total));

                let adresse_user = resultats.adresse_user;
                let texte_adresse_user = adresse_user.prenom + " " + adresse_user.nom + "\n";
                texte_adresse_user += adresse_user.adresse + "\n";
                texte_adresse_user += adresse_user.code_postal + " " + adresse_user.ville;

                $("#modale-adresse-facturation").text(texte_adresse_user);

                if(resultats.adresse_livraison == null) {

                    $("#modale-adresse-livraison").text(texte_adresse_user);
                }

                else {

                    let adresse_livraison = resultats.adresse_livraison;
                    let texte_adresse_livraison = adresse_livraison.prenom + " " + adresse_livraison.nom + "\n";
                    texte_adresse_livraison += adresse_livraison.adresse + "\n";
                    texte_adresse_livraison += adresse_livraison.code_postal + " " + adresse_livraison.ville;
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

    function convertiDates(date) {

        date = date.date;
        date = date.split(" ");
        date = date[0];
        date = date.split("-");
        //attention: les mois sont numerotes de 0 à 11 dans les dates js
        date = new Date(date[0], date[1]-1, date[2]);
        return date;
    }

    function formatteNombrePourAffichage(nombre) {

        nombre /= 100;
        nombre = nombre.toLocaleString('fr');

        let secu = nombre.split(",");
        if(secu.length == 1) {

            nombre = nombre + ",00";
        }

        else if(secu[1].length == 1) {

            nombre += "0";
        }

        return nombre;
    }

    function loader(show) {

        if(show) {

            $("#loader").show();
        }

        else {
            
            $("#loader").hide();
        }
    }
});