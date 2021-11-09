$(document).ready(function() {

    var stripe = Stripe(stripe_pk);
    var elements = stripe.elements();
    var numero_carte = elements.create('cardNumber');
    numero_carte.mount('#numero-carte');
    var expiration_carte = elements.create('cardExpiry', {placeholder: "MM/AA"});
    expiration_carte.mount('#expiration-carte');
    var crypto_carte = elements.create('cardCvc', {placeholder: "CVC / Cryptogramme"});
    crypto_carte.mount('#crypto-carte');

    if($("#adresse_livraison_nom").prop("value") !== "") {
        $("#checkbox-livraison").prop("checked", true);
        $("#col-livraison").slideDown();
        $("#col-livraison").toggleClass("collapse");
    } else {
        $("#checkbox-livraison").prop("checked", false);
    }

    $("#checkbox-livraison").on("click", function() {
        if($(this).prop("checked")) {
            $("#col-livraison").slideDown();
        } else {
            $("#col-livraison").slideUp();
        }
        $("#col-livraison").toggleClass("collapse");
    })

    $(".champ-adresse").on("change", function() {
        if($("#rappel-validation").hasClass("collapse")) {
            $("#rappel-validation").toggleClass("collapse");
        }

        $("#li-expedition").removeClass("d-none");
    })

    $(".champ-message").on("change", function() {
        if($("#rappel-validation").hasClass("collapse")) {
            $("#rappel-validation").toggleClass("collapse");
        }

        $("#li-message").removeClass("d-none");
    })

    //soumission adresse de livraison differente
    $("body").on("click", "#bouton_envoi", function(event) {
        event.preventDefault();

        if(!$("#li-expedition").hasClass("d-none")) {
            $("#li-expedition").addClass("d-none");

            if($("#li-message").hasClass("d-none")) {
                $("#rappel-validation").toggleClass("collapse");
            }
        }

        loader(true);
        let form = $("[name = 'adresse_livraison']");
        let form_data = form.serializeObject();

        $.ajax({
            type: "POST",
            url: racine + "commun/adresse_livraison_custom",
            dataType: 'json',
            data: form_data,
            success: function(data) {
                if(typeof(data.output) === "undefined") {
                    toastr.success("Adresse de livraison enregistree");
                } else {
                    $("#col-livraison").empty();
                    $("#col-livraison").append("<span id='consigne-formulaire'>Veuillez renseigner tout les champs</span>");
                    $("#col-livraison").append(data.output);
                }

                loader(false);
            },
            error: function(err) {
                toastr.error("Erreur: un probleme est survenu pendant l'enregisrement de l'adresse de livraison");
                //console.log(err);
                loader(false);
            }
        })
    })

    //retire le style des champs invalides
    $("body").on("click", ".champ", function() {
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

    //soumission message pour la livraison
    $("body").on("click", "#bouton-message", function(event) {
        event.preventDefault();

        if(!$("#li-message").hasClass("d-none")) {
            $("#li-message").addClass("d-none");

            if($("#li-expedition").hasClass("d-none")) {
                $("#rappel-validation").toggleClass("collapse");
            }
        }

        loader(true);
        let form = $("[name = 'message_livraison']");
        let form_data = form.serializeObject();

        $.ajax({
            type: "POST",
            url: racine + "commun/message_livraison",
            dataType: 'json',
            data: form_data,
            success: function(data) {
                if(typeof(data.output) === "undefined") {
                    toastr.success("Votre message a bien été enregistré");
                } else {
                    $("#td-message-livraison").empty();
                    $("#td-message-livraison").append(data.output);
                }

                loader(false);
            },
            error: function(err) {
                toastr.error("Erreur: un probleme est survenu pendant l'enregisrement de votre message");
                //console.log(err);
                loader(false);
            }
        });
    })

    $("#utiliser-carte-enregistree").on("click", function() {
        $("#checkbox-remember").prop("checked", false);
    })

    $("#checkbox-remember").on("click", function() {
        $("#utiliser-nouvelle-carte").prop("checked", true);
    })

    $("#carte_enregistree").on("click", function() {
        $("#utiliser-carte-enregistree").prop("checked", true);
    });

    numero_carte.on("change", function() {
        $("#utiliser-nouvelle-carte").prop("checked", true);
    });

    expiration_carte.on("change", function() {
        $("#utiliser-nouvelle-carte").prop("checked", true);
    });

    crypto_carte.on("change", function() {
        $("#utiliser-nouvelle-carte").prop("checked", true);
    });

    $("#bouton-paiement").on("click", function() {
        let data = {
            conditions_lues: $("#checkbox-conditions").prop("checked"),
            adresse_differente: $("#checkbox-livraison").prop("checked"),
        };
        //utiliser carte enregistree
        if($("#utiliser-carte-enregistree").length && $("#utiliser-carte-enregistree").is(':checked')) {

            data.payment_method_id = $("#carte_enregistree").val();
        }

        if($("#checkbox-conditions").prop("checked") === false) {
            popoverShow();
        } else {
            loader(true);
            $.ajax({
                type: "POST",
                url: "/paniers/paiement_post", //utilisation d'une url du type racine + "/mon_url" provoque une erreur
                data: data,
                success: function(response) {
                    if(response.conditions_lues == false) {
                        $("#bouton-paiement").popover('show');
                    } else {
                        //on paie avec une carte enregistrée
                        if($("#carte_enregistree").val() != null && $("#utiliser-carte-enregistree").is(':checked')) {
                            stripe.confirmCardPayment(response.client_secret, {
                                payment_method: response.erreur
                            })
                            .then(function(result) {
                                let form = document.createElement('form');
                                document.body.appendChild(form);
                                form.method = 'post';
                                form.action = "/paniers/paiement_fail";
                                var input = document.createElement('input');
                                input.type = 'hidden';

                                if (result.error) {
                                    input.name = "erreur";
                                    input.value = result.error.message;
                                    form.appendChild(input);
                                    form.submit();
                                } else {
                                    if(result.paymentIntent.status === 'succeeded') {
                                        form.action = "/paniers/paiement_success";
                                        input.name = "message";
                                        input.value = "Votre paiement a bien été effectué";
                                        form.appendChild(input);
                                        form.submit();
                                    }
                                }
                            });
                        } else { //on paie avec une carte non enregistree
                            
                            let secret = response.client_secret;
                            let num_carte = elements._elements[0];
                
                            stripe
                            .confirmCardPayment(secret, {
                                payment_method: {
                                    card: num_carte,
                                    billing_details: {
                                        email: user_mail
                                    },
                                },
                                setup_future_usage: 'off_session',
                                save_payment_method: true
                            })
                            .then(function(result) {
                                if(typeof result.error != "undefined") {
                                    switch(result.error.code) {
                                        case "invalid_number":
                                            $("#numero-carte").parent().addClass("invalid-input");
                                            break;
            
                                        case "expired_card":
                                            $("#expiration-carte").parent().addClass("invalid-input");
                                            break;
            
                                        case "incorrect_cvc":
                                            $("#crypto-carte").parent().addClass("invalid-input");
                                            break;
            
                                        default:
                                            break;
                                    }
            
                                    toastr.error(result.error.message);
                                    loader(false);
                                } else {
                                    let form = document.createElement('form');
                                    document.body.appendChild(form);
                                    form.method = 'post';
                                    form.action = "/paniers/paiement_success";
                                    var input = document.createElement('input');
                                    input.type = 'hidden';
            
                                    //enregistrement de la carte utilisée (si demandé)
                                    if($("#checkbox-remember").prop("checked")) {
                                        $.ajax({
                                            type: "POST",
                                            url: "/paniers/sauvecarte",
                                            success: function(){
                                                input.value = "Votre paiement et l'enregistrement de votre carte ont bien été effectués";
                                                form.appendChild(input);
                                                form.submit();
                                            },
                                            error: function() {
                                                input.value = "Attention: votre paiement a bien été effectué mais l'enregistrement de votre carte a échoué";
                                                form.appendChild(input);
                                                form.submit();
                                            }
                                        })
                                    } else {
                                        input.value = "Votre paiement a bien été effectué";
                                        form.appendChild(input);
                                        form.submit();
                                    }
                                }
                            });
                        }
                    }
                },
                error: function(err) {
                    //console.log(err);
                    toastr.error("Erreur: un problème est survenu. Veuillez raffraichir la page");
                    loader(false);
                }
            })
        }
      })

    $("#label-conditions").on("click", function() {
        $("#bouton-paiement").popover('hide');
    });

    function popoverShow() {
        $("#bouton-paiement").attr("aria-disabled", "true");
        $("#bouton-paiement").attr("data-bs-toggle", "popover");
        $("#bouton-paiement").attr("data-bs-trigger", "manual");
        $("#bouton-paiement").attr("data-bs-placement", "left");
        $("#bouton-paiement").attr("data-bs-content", "Veuillez lire et accepter les conditions générales de vente");
        $("#bouton-paiement").popover('show');
    }

    function loader(show) {
        if(show) {
            $("#loader").show();
        } else {
            $("#loader").hide();
        }
    }
})