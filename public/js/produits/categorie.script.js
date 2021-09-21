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


        /*
        console.log(categorie); //categorie 1
        console.log(sous_categorie); //sous categorie 1 || null
        console.log(numero_page); //2
        */

        //@TODO gerer requete ac utilisation #filtre
        $.ajax({
            type: "POST",
            url: racine + "produits/more",
            data: {
                categorie: categorie,
                sous_categorie: sous_categorie,
                numero_page: numero_page,
            },
            success: function(response) {

                page_actuelle = numero_page;
                let produits = JSON.parse(response);
                //$("#rang-produit").empty();

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
                
                //ori
                /*for(produit of produits) {

                    ajoutCarte(produit);
                }*/

                //essai
                ajoutCartes(produits);

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
        })
    })

    //fct nouvelle mouture
    function ajoutCartes(produits) {
        
        let cible = $(".case-produit:last");
        //let copie = cible.clone(); //clonage derniere carte affichee
        $("#rang-produit").empty(); //suppr ttes cartes affichees


        for(produit of produits) {

            let copie = cible.clone(); //clonage derniere carte affichee

            //console.log(produit);

            /*var link = copie.find('.lien-article:first').attr("href");
            console.log(link);*/

            //gestion lien a
            //copie.find('.lien-article:first').attr("href", produit.nom); //lien
            copie.find('.lien-article').attr("href", produit.nom); //lien

            //gestion image affichee + alt
            let image = produit.images[0];
            image = image.image;
            copie.find('.card-img-top').attr("src", racine + 'images/produits/' + image);
            copie.find('.card-img-top').attr("alt", 'image ' + produit.nom);

            //gestion "titre" (nom produit)
            copie.find('.nom-produit').html(produit.nom);

            //test affichage date
            /*if(produit.date_debut_promo !== null) {

                //console.log(typeof(produit.date_debut_promo));  //simple date
                let essai = new Date(produit.date_debut_promo);
                console.log(essai);

                /*console.log(produit.date_debut_promo);
                copie.find('.nom-produit').html(produit.date_debut_promo);*
            }*/

            //--
            let taux_tva = 0;
            if(produit.tva_active) {
                taux_tva = produit.taux_tva.taux;
            }

            //(produit.tarif/100 + (produit.tarif/100 * tva/10000))
            let tarif_standard = produit.tarif/100 + (produit.tarif/100 * taux_tva/10000);
            //tarif promo idem ac produit.tarif_promo
            tarif_standard = CurrencyFormatted(tarif_standard);

            let now = new Date();
            let date_debut_promo = new Date(produit.date_debut_promo);
            let date_fin_promo = new Date(produit.date_fin_promo);

            // !! cas ou carte clonee est "promo" -> elements en 'trop' pr non promo
            // et inversement, si clone non promo, des elements manqueront en cas promo

            //{% if produit.getDateDebutPromo()|date('Y-m-d') <= "now"|date('Y-m-d') and produit.getDateFinPromo()|date('Y-m-d') >= "now"|date('Y-m-d') %}
            /*if(date_debut_promo <= now && date_fin_promo >= now) {
                //affichage prix mode promo
                let tarif_promo = produit.tarif_promo/100 + (produit.tarif_promo/100 * taux_tva/10000);
                tarif_promo = CurrencyFormatted(tarif_promo);
                copie.find('del').html("&euro;"+tarif_standard);
                copie.find('ins').html("&euro;"+tarif_promo);

                //const options = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' };
                //console.log(date_debut_promo.toLocaleDateString('fr-FR', options));
                //console.log(date_debut_promo.toLocaleDateString('fr-FR'));

                copie.find(".prix-produit:last").html("promotion valable jusqu'au " + date_fin_promo.toLocaleDateString('fr-FR'));
            } else {
                //pas promo
                copie.find(".prix-produit:first").html("&euro;"+tarif_standard);
            }*/

            //-- tout cramer pour repartir sur des bases saines
            copie.find(".prix-produit").remove();
            copie.find('.card-body').append("<p class='prix-produit'></p>");

            if(date_debut_promo <= now && date_fin_promo >= now) {

                let tarif_promo = produit.tarif_promo/100 + (produit.tarif_promo/100 * taux_tva/10000);
                tarif_promo = CurrencyFormatted(tarif_promo);
                let del = $("<del></del>").html("&euro;"+tarif_standard);
                let ins = $("<ins></ins>").html("&euro;"+tarif_promo);
                //copie.find('.card-body').append("<p class='prix-produit'></p>");
                copie.find('.prix-produit').append(del); //nbsp; apres del a inserer <=======================
                copie.find('.prix-produit').append(ins);
                copie.find('.prix-produit').append("<br>");

                copie.find('.card-body').append("<p class='prix-produit text-center'></p>");
                copie.find('.prix-produit:last').html("promotion valable jusqu'au " + date_fin_promo.toLocaleDateString('fr-FR'));
            } else {
                copie.find(".prix-produit").html("&euro;"+tarif_standard);
            }
            //--


            /*
            //console.log(produit.date_debut_promo);  //2020-07-08T00:00:00+02:00
            let date_promo = produit.date_debut_promo;  //2020-07-08T00:00:00+02:00
            let currentDate = new Date();  //Date Tue Sep 21 2021 14:05:41 GMT+0200 (heure d’été d’Europe centrale) => objet date
            //console.log(currentDate);

            //comparaison semblent marcher
            if(date_promo < currentDate) {
                console.log("case 174");
            } else {
                console.log("case 176");
            }*/

            $("#rang-produit").append(copie); //last line
        }



        
    }


    //fct originale
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

    //recup stackoverflow
    function CurrencyFormatted(amount)
    {
        var i = parseFloat(amount);
        if(isNaN(i)) { i = 0.00; }
        var minus = '';
        if(i < 0) { minus = '-'; }
        i = Math.abs(i);
        i = parseInt((i + .005) * 100);
        i = i / 100;
        s = new String(i);
        if(s.indexOf('.') < 0) { s += '.00'; }
        if(s.indexOf('.') == (s.length - 2)) { s += '0'; }
        s = minus + s;
        return s;
    }
    //fin recup

    function loader(show) {

        if(show) {

            $("#loader").show();
        }

        else {
            
            $("#loader").hide();
        }
    }
})