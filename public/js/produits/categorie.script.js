$(document).ready(function() {  
    let page_actuelle = 1;

    $("#filtre").on("change", function() {
        loader(true);
        let numero_page = page_actuelle;

        $.ajax({
            type: "POST",
            url: racine + "produits/more",
            data: {
                categorie: categorie,
                sous_categorie: sous_categorie,
                numero_page: numero_page,
                tri: $(this).val()
            },
            success: function(response) {
                let produits = JSON.parse(response);
                ajoutCartes(produits);
                loader(false);
            },
            error: function(err) {
                loader(false);
                console.log(err);
            }
        })
    })

    $(".page-navigation").on("click", function() {

        loader(true);
        let cible = $(this).parent().closest("li");

        $(".page-item").each(function() {

            $(this).removeClass("active");
        })

        cible.addClass("active");
        let numero_page = $(this).data("number");

        if(typeof numero_page == "undefined") {

            if($(this).parent().attr("id") == "fleche-dernier") {

                numero_page = Math.ceil(quantite_totale/qte_max_articles_affiches);
                $(document).find(".page-number").last().addClass("active");
            } else {
                numero_page = 1;
                $(document).find(".page-number").first().addClass("active");
            }
        }

        $.ajax({
            type: "POST",
            url: racine + "produits/more",
            data: {
                categorie: categorie,
                sous_categorie: sous_categorie,
                numero_page: numero_page,
                tri: $("#filtre").val(),
            },
            success: function(response) {

                page_actuelle = numero_page;
                let produits = JSON.parse(response);

                if(numero_page == 1) {
                    $("#fleche-premier").addClass("d-none");
                } else {
                    $("#fleche-premier").removeClass("d-none");
                }

                if(numero_page == Math.ceil(quantite_totale/qte_max_articles_affiches)) {
                    $("#fleche-dernier").addClass("d-none");
                } else {
                    $("#fleche-dernier").removeClass("d-none");
                }
                
                ajoutCartes(produits);

               let premier = qte_max_articles_affiches * (numero_page - 1) + 1;
               let dernier = qte_max_articles_affiches * numero_page;

               if(dernier > quantite_totale) {
                    dernier = quantite_totale;
               }

               window.scrollTo(0, 0); 

               $("#texte-resultats").text("Affichage de " + premier + "-" + dernier + " sur " + quantite_totale + " résultat(s)");

                loader(false);
            },
            error: function(err) {
                loader(false);
                console.log(err);
            }
        })
    })

    function ajoutCartes(produits) {
        //clonage de la derniere card qui presente un produit
        //pour chaque produit, modification du clone avec les info du produit en cours
        //puis insert dans la page
        let cible = $(".case-produit:last");
        $("#rang-produit").empty();

        for(produit of produits) {
            let copie = cible.clone();
            copie.find('.lien-article').attr("href", produit.nom);
            let image = produit.images[0];
            image = image.image;
            copie.find('.card-img-top').attr("src", racine + 'images/produits/' + image);
            copie.find('.card-img-top').attr("alt", 'image ' + produit.nom);
            copie.find('.nom-produit').html(produit.nom);
            let taux_tva = 0;

            if(produit.tva_active) {
                taux_tva = produit.taux_tva.taux;
            }

            let tarif_standard = produit.tarif/100 + (produit.tarif/100 * taux_tva/10000);
            tarif_standard = CurrencyFormatted(tarif_standard);
            let now = new Date().getTime();
            let date_debut_promo = convertiDates(produit.date_debut_promo);
            let date_fin_promo = convertiDates(produit.date_fin_promo);

            //la partie avec le/les prix differere si promo en cours ou pas
            //on detruit cette partie du clone et on la reconstruit pour chaque produit selon presence / absence promo
            copie.find(".prix-produit").remove();
            copie.find('.card-body').append("<p class='prix-produit'></p>");

            if(date_debut_promo <= now && date_fin_promo >= now) {
                let tarif_promo = produit.tarif_promo/100 + (produit.tarif_promo/100 * taux_tva/10000);
                tarif_promo = CurrencyFormatted(tarif_promo);
                let del = $("<del></del>").html("&euro;"+tarif_standard);
                let ins = $("<ins></ins>").html("&euro;"+tarif_promo);
                copie.find('.prix-produit').append(del);
                let pwet = $("<span></span>").html("&nbsp;&nbsp;");
                copie.find('.prix-produit').append(pwet);
                copie.find('.prix-produit').append(ins);
                copie.find('.prix-produit').append("<br>");
                copie.find('.card-body').append("<p class='prix-produit text-center'></p>");
                copie.find('.prix-produit:last').html("promotion valable jusqu'au " + date_fin_promo.toLocaleDateString('fr-FR'));
            } else {
                copie.find(".prix-produit").html("&euro;"+tarif_standard);
            }

            $("#rang-produit").append(copie);
        }        
    }

    function convertiDates(date) {
        let date_js = new Date(date).getTime();
        return date_js.toLocaleString('fr-FR');
    }

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
})