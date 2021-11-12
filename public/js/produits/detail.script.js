$(document).ready(function() {
    //sans lui, les popover ne marchent pas
    $('[data-toggle="popover"]').popover();

    //evite des erreurs dans la console dans le cas ou aucun produit n'a ete trouvé
    if($("#form_id_produit").length > 0) {
        $("#form_id_produit").val(id_produit);
    }

    $(window).on("resize", function() {
        if($(window).width() < 992) {
            $("#container-principal").addClass("container-fluid");
            $("#container-principal").removeClass("container");
        } else {
            $("#container-principal").addClass("container");
            $("#container-principal").removeClass("container-fluid");
        }
    });

    $("#case-image-principale").on("mousemove", function(event) {
        let relX = event.pageX - $(this).offset().left;
        let relY = event.pageY - $(this).offset().top;

        $("#photo-principale").css({
            "left" : -relX,
            "top" : -relY
        });
    })

    $("#case-image-principale").on("mouseenter", function() {
        $("#photo-principale").css({
            "left": 0,
            "top": 0,
            "transform": "scale(2.0)",
            "transform-origin": "left top"
        });
    })

    $("#photo-principale").on("mouseleave", function() {
        $(this).css({
            "left": 0,
            "top": 0,
            "transform": "scale(1.0)"
        });
    })
    
    $("#zoom-principale").on("click", function() {
        $(this).hide();
        $("#modale-image").modal("show");
    })

    $("#modale-image").on("hidden.bs.modal", function() {
        $("#zoom-principale").show();
    })

    $(".image-galerie").on("click", function() {
        let index = $(this).data("index");
        
        (".carousel-item").search(function() {
            $(this).removeClass("active");
        })

        $("#carousel_" + index).addClass("active");
        $("#photo-principale").attr("src", $(this).attr("src"));
    })

    let tempo;
    secu_bulle = false;

    function resetTempo() {
        if(tempo != null) {
            clearTimeout(tempo);
        }
    }

    $("#plus").on("mouseenter", function() {
        resetTempo();
        $(this).popover("show");

        $("#social-content").on("mouseenter", function() {
            resetTempo();
            secu_bulle = true;
        })

        $("#social-content").on("mouseleave", function() {
            secu_bulle = false;
            $("#plus").trigger("mouseleave");
        })
    })

    $("#plus").on("mouseleave", function() {
        if(!secu_bulle) {
            tempo = setTimeout(function() {

                $("#plus").popover("hide");
            }, 700);
        }        
    })

    $("#ajout-panier").on("click", function() {
        loader(true);
        let quantite_editee = $("#quantite-ajout-panier").val();
        
        $.ajax({
            type: "POST",
            url: racine + "paniers/modifie-quantite",
            data: {
                id_produit: id_produit,
                quantite: quantite_editee,
                mode: "ajout"
            },
            success: function(response) {
                response = JSON.parse(response);

                if(typeof(response.erreur) !== "undefined") { //cas ou le user est un bricoleur
                    toastr.error(response.erreur);
                } else {
                    //si plus de stock et non commandable sans stock
                    if(!response.produit_dispo_sans_stock &&
                        (response.quantite_finale_produit >= response.quantite_produit_stock || response.quantite_produit_stock === 0)
                    ) {
                        $("#quantite-ajout-panier").val(0);
                        $("#quantite-ajout-panier").attr("disabled", true);
                        $("#ajout-panier").attr("disabled", true);

                        let message = "<div class='col-12 p-3'>"
                        message += "Ce produit n'est actuellement plus disponible";
                        message += "</div>";
                        $(".rang-detail").last().append(message);
                    } else {
                        $("#quantite-ajout-panier").val(1);
                        toastr.success("Produit ajouté au panier");
                    }

                    if(!response.produit_dispo_sans_stock && !$("#quantite-ajout-panier").attr("disabled")) {
                        $("#quantite-ajout-panier").attr("max", response.quantite_produit_stock - response.quantite_finale_produit);
                    }

                    $("#compteur-panier").text(response.nombre_articles_panier);
                }
                loader(false);
            },
            error: function(err) {
                //console.log(err);
                loader(false);
                toastr.error("Erreur ajout produit au panier");
            }
        })
    })
    
    //soumission message pour la livraison
    $("body").on("click", "#validation-avis", function(event) {
        event.preventDefault();
        loader(true);
        let form = $("[name = 'avis']");
        let form_data = form.serializeObject();

        $.ajax({
            type: "POST",
            url: racine + "avis/formulaire/" + id_produit,
            dataType: 'json',
            data: form_data,
            success: function(data) {
                if(typeof(data.output) === "undefined") {
                    nombre_avis++;
                    ajoutAvis($("#avis_commentaire").val(), $("#avis_note").val());
                    $("#nav-avis-tab").text("Avis (" + nombre_avis + ")");
                    $("#avis_note").val("5");
                    $("#avis_commentaire").val("");
                    toastr.success("Votre avis a été enregistré. Merci de votre participation");
                } else {
                    $("#div-form-avis").empty();
                    $("#div-form-avis").append(data.output);
                }

                loader(false);
            },
            error: function(err) {
                toastr.error("Erreur: un probleme est survenu pendant l'enregisrement de votre avis");
                //console.log(err);
                loader(false);
            }
        });
    })

    function ajoutAvis(avis, note) {
        let classe_row = "row rang-avis";

        if(nombre_avis%2 === 0) {
            classe_row += " fond-gris";
        }

        let add = "<div class='" + classe_row + "'>";
        add += "<div class='col-md-3 col-6 case-note'>";

        for(let i=1; i<6; i++) {
            if(i <= note) {
                add += "<i class='fas fa-star etoile'>&nbsp;</i>";
            } else {
                add += "<i class='far fa-star etoile'>&nbsp;</i>";
            }
        }

        add += "</div>";
        add += "<div class='col-md-9 col-6'>" + user + "</div></div>";
        add += "<div class='" + classe_row + "'>";
        add += "<div class='col-md-9 offset-md-3 col-12 commentaire'>";
        add += avis + "</div></div>";
        $("#container-avis").append(add);
    }

    //retire le style des champs invalides
    $("body").on("click", ".champ-avis", function() {
        let element_suivant = $(this).next();
        
        if(element_suivant.hasClass("invalid-feedback")) {
            //retrait du style champ incorrect
            $(this).removeClass("is-invalid");
            element_suivant.remove();
        }
    })

    $.fn.serializeObject = function() {
        var o = {};
        var a = this.serializeArray();
        $.each(a, function() {
            if (o[this.name]) {
                if (!o[this.name].push) {
                    o[this.name] = [o[this.name]];
                }
                o[this.name].push(this.value || '');
            } else {
                o[this.name] = this.value || '';
            }
        });
        return o;
    };

    function loader(show) {
        if(show) {
            $("#loader").show();
        } else {
            $("#loader").hide();
        }
    }
})