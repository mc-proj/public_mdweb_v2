$(document).ready(function() {  

    let page_actuelle = 1;

    $("#filtre").on("change", function() {

        loader(true);
        let numero_page = page_actuelle;

        $.ajax({
            type: "POST",
            url: "/boutique/more",
            data: {
                categorie: categorie,
                sous_categorie: sous_categorie,
                numero_page: numero_page,
                tri: $(this).val()
            },
            success: function(response) {

                let produits = JSON.parse(response);
                $("#rang-produit").empty();
                
                for(produit of produits) {

                    ajoutCarte(produit);
                    console.log(produit.nom);
                }

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

                numero_page = Math.ceil(quantite_totale/16);
                $(document).find(".page-number").last().addClass("active");
            }

            else {

                numero_page = 1;
                $(document).find(".page-number").first().addClass("active");
            }
        }


        console.log(categorie); //categorie 1
        console.log(sous_categorie); //sous categorie 1 || null
        console.log(numero_page); //2

        //@TODO gerer requete ac utilisation #filtre
        /*$.ajax({
            type: "POST",
            url: "/boutique/more",
            data: {
                categorie: categorie,
                sous_categorie: sous_categorie,
                numero_page: numero_page,
            },
            success: function(response) {

                page_actuelle = numero_page;
                let produits = JSON.parse(response);
                $("#rang-produit").empty();

                if(numero_page == 1) {

                    $("#fleche-premier").addClass("d-none");
                }

                else {

                    $("#fleche-premier").removeClass("d-none");
                }

                if(numero_page == Math.ceil(quantite_totale/16)) {

                    $("#fleche-dernier").addClass("d-none");
                }

                else {

                    $("#fleche-dernier").removeClass("d-none");
                }
                
                for(produit of produits) {

                    ajoutCarte(produit);
                }

               let premier = 16 * (numero_page - 1) + 1;
               let dernier = 16 * numero_page;

               if(dernier > quantite_totale) {

                    dernier = quantite_totale;
               }

               window.scrollTo(0, 0); 

               $("#texte-resultats").text("Affichage de " + premier + "-" + dernier + " sur " + quantite_totale + " résultat(s)");

                loader(false);
            },
            error: function(err) {

                console.log(categorie + '|' + sous_categorie + '|' + numero_page);
                loader(false);
                console.log(err);
            }
        })*/
    })

    function ajoutCarte(produit) {

        let currentDate = new Date();
        let cDay = currentDate.getDate();
        let cMonth = currentDate.getMonth() + 1;
        let cYear = currentDate.getFullYear();
        let now = cYear + "-" + cMonth + "-" + cDay;
        let promo_valide = false;
        let debut_promo = produit.date_debut_promo.date;
        let fin_promo = produit.date_fin_promo.date;
        let tarif = produit.tarif/100;
        let tarif_promo = produit.tarif_promo/100;
        let tva = 0;
        let tva_promo = 0;

        if(produit.etat_tva) {

            tva = tarif  * produit.taux_tva/10000;
            tva_promo = tarif_promo  * produit.taux_tva/10000;
        }

        tarif += tva;
        tarif = formatteNombre(tarif);
        tarif_promo += tva_promo;
        tarif_promo = formatteNombre(tarif_promo);
        tarif = tarif.replace('.', ',');
        tarif_promo = tarif_promo.replace('.', ',');

        if(debut_promo != null && fin_promo != null) {

            debut_promo = debut_promo.split(" ");
            debut_promo = debut_promo[0];
            fin_promo = fin_promo.split(" ");
            fin_promo = fin_promo[0];

            if((debut_promo <= now) && (now <= fin_promo)) {

                promo_valide = true;
            }
        }

        let ajout = "<div class='col-md-3 col-6 case-produit'>";
        let s_categorie = "";

        if(sous_categorie != null) {

            s_categorie = sous_categorie + "/";
        }

        ajout += "<a href='/boutique/" + categorie + "/" + s_categorie + produit.nom + "' class='lien-article'>";
        ajout += "<div class='card shadow'>";
        ajout += "<img src='../../images/produits/" + produit.images[0] + "' class='card-img-top' alt='image " + produit.nom + "'>";
        ajout += "<div class='card-body'>";
        ajout += "<p class='nom-produit'>" + produit.nom + "</p>";
        ajout += "<p class='prix-produit'>";

        if(promo_valide) {

            fin_promo = formateDate(fin_promo);
            ajout += "<del>&euro;" + tarif + "</del>";
            ajout += "&nbsp;<ins>&euro;" + tarif_promo + "</ins><br>";
            ajout += "<span class='prix-produit'>promotion valable jusqu'au " + fin_promo + "</span>";
        }

        else {

            ajout += "&euro;" + tarif;
        }

        ajout += "</p></div></div></a></div>";
        $("#rang-produit").append(ajout);
    }

    function formateDate(date_recue) {

        date_recue = date_recue.split("-");
        date_recue = date_recue[2] + "/" + date_recue[1] + "/" + date_recue[0];
        return date_recue;
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