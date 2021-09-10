$(document).ready(function() {

    //sans lui, les popover ne marchent pas
    $('[data-toggle="popover"]').popover();

    //evite des erreurs dans la console dans le cas ou aucun produit n'a ete trouvé
    if($("#form_id_produit").length > 0) {

        $("#form_id_produit").val(id_produit);
    }

    $(".message-flash").on("click", function() {

        $(this).hide();
    })

    setTimeout(function() {

        $(".message-flash").hide();
    }, 3000);

    $(window).on("resize", function() {

        if($(window).width() < 992) {

            $("#container-principal").addClass("container-fluid");
            $("#container-principal").removeClass("container");
        }

        else {

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
        let entree  = $("#quantite-ajout-panier").val();
        entree = parseInt(entree);
        let max = $("#quantite-ajout-panier").attr("max");
        max = parseInt(max);

        if(entree > max) {

            $("#quantite-ajout-panier").val($("#quantite-ajout-panier").attr("max"));
        }
        
        $.ajax({
            type: "POST",
            url: "/panier/modifie-quantite",
            data: {

                id_produit: id_produit,
                quantite: $("#quantite-ajout-panier").val(),
                mode: "ajout"
            },
            success: function(response) {

                response = JSON.parse(response);
                
                if(response.nombre_articles != false) {

                    //securite: cas ou le user modifie le front pour entrer une quantite superieure au stock
                    //le back renvoie la quantite reelle
                    if($("#quantite-ajout-panier").val() > response.quantite_finale_produit) {

                        $("#quantite-ajout-panier").val(response.quantite_finale_produit);
                    }

                    $("#compteur-panier").text(response.nombre_articles);
                    toastr.success("Produit ajouté au panier");
                }

                else {

                    toastr.error("Erreur ajout produit au panier");
                }

                loader(false);
            },
            error: function(err) {

                console.log(err);
                loader(false);
                toastr.error("Erreur ajout produit au panier");
            }
        })
    })

    function loader(show) {

        if(show) {

            $("#loader").show();
        }

        else {
            
            $("#loader").hide();
        }
    }
})