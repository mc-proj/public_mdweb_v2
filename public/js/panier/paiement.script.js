$(document).ready(function() {

    var stripe = Stripe(stripe_pk);
    var elements = stripe.elements();
    var numero_carte = elements.create('cardNumber');
    numero_carte.mount('#numero-carte');
    var expiration_carte = elements.create('cardExpiry', {placeholder: "MM/AA"});
    expiration_carte.mount('#expiration-carte');
    var crypto_carte = elements.create('cardCvc', {placeholder: "CVC / Cryptogramme"});
    crypto_carte.mount('#crypto-carte');
    
    //decoche la checkbox en cas de f5
    $("#checkbox-livraison").prop("checked", false);

    $("#checkbox-livraison").on("click", function() {

        if($(this).prop("checked")) {

            $("#col-livraison").slideDown();
        }

        else {

            $("#col-livraison").slideUp();
        }

        $("#col-livraison").toggleClass("collapse");
    })

    //permet utilisation .serializeObject()
    $.fn.serializeObject = function()
    {
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

    $("#btn-submit").on("click", function() {

        let form = $("[name = 'form']");
        // need $.fn.serializeObject
        let form_data = form.serializeObject();
        let champs = [];
        loader(true);

        $(".champ").each(function() {

            let id = $(this).attr("id");
            let input = id.split("form_");
            champs.push(input[1]);
        })

        $.ajax({

            type: "POST",
            url: "/panier/paiement",
            dataType: 'json',
            data: form_data,
            success: function(data) {

                let erreurs = data.erreurs;
                let valeurs_entrees = data.valeurs_entrees;

                if(valeurs_entrees != []) {

                    for(champ of champs) {

                        let input = "#form_" + champ;
                        $(input).val(valeurs_entrees[champ]);

                        //le champ en cours n'a pas d'erreur
                        if(typeof erreurs[champ] == "undefined") {

                            //si un message d'erreur est present, on le supprime
                            if($(input).siblings(".texte-rouge").length != 0) {

                                $(input).siblings(".texte-rouge").remove();
                            }
                        }

                        else {

                            let form_group = $(input).parent();

                            //si un message d'erreur est deja present pour le champ en cours, on edite son contenu
                            if($(input).siblings(".texte-rouge").length != 0) {

                                $(input).siblings(".texte-rouge").html("<span class='badge badge-danger'>ERREUR</span> " + erreurs[champ]);
                            }

                            //si aucun message d'erreur n'est encore present, on l'ajoute
                            else {

                                form_group.prepend("<div class='texte-rouge'><span class='badge badge-danger'>ERREUR</span> " + erreurs[champ] + "</div>");
                            }
                        }
                    }
                }

                else {

                    toastr.success("Adresse de livraison enregistree");
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

    $("#bouton-message").on("click", function() {

        let form = $("[name = 'form-message']");
        //need $.fn.serializeObject
        let form_data = form.serializeObject();
        loader(true);

        $.ajax({

            type: "POST",
            url: "/panier/soumetmessage",
            dataType: 'json',
            data: form_data,
            success: function(response) {

                if(response != "ok") {

                    toastr.error("Erreur: votre message doit comporter 255 caracteres maximum");
                }

                else {

                    toastr.success("Votre message a bien été enregistré");
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

    $("#bouton-paiement").on("click", function() {

        let data = {

            conditions_lues: $("#checkbox-conditions").prop("checked"),
            adresse_differente: $("#checkbox-livraison").prop("checked")
        };
        //utiliser carte enregistree
        if($("#utiliser-carte-enregistree").length && $("#utiliser-carte-enregistree").is(':checked')) {

            data.payment_method_id = $("#carte_enregistree").val();
        }

        loader(true);
        
        $.ajax({
  
          type: "POST",
          url: "/panier/paiement_post",
          data: data,
          success: function(response) {

            if(response.conditions_lues == false) {

                $("#bouton-paiement").popover('show');
            }

            else {

                //on paie avec une carte enregistrée
                if($("#carte_enregistree").val() != null && $("#utiliser-carte-enregistree").is(':checked')) {

                    stripe.confirmCardPayment(response.client_secret, {
    
                            payment_method: response.erreur
                        })
                        .then(function(result) {

                            let form = document.createElement('form');
                            document.body.appendChild(form);
                            form.method = 'post';
                            form.action = "/panier/paiement_fail";
                            var input = document.createElement('input');
                            input.type = 'hidden';
                            form.action = "/panier/paiement_fail";


                            if (result.error) {

                                input.name = "erreur";
                                input.value = result.error.message;
                                form.appendChild(input);
                                form.submit();
                            }
                        
                        else {
    
                            if (result.paymentIntent.status === 'succeeded') {

                                form.action = "/panier/paiement_success";
                                input.name = "message";
                                input.value = "Votre paiement a bien été effectué";
                                form.appendChild(input);
                                form.submit();
                            }
                        }
                    });
                }
    
                //on paie avec une carte non enregistree
                else {
                    
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
                        }
    
                        else {

                            let form = document.createElement('form');
                            document.body.appendChild(form);
                            form.method = 'post';
                            form.action = "/panier/paiement_success";
                            var input = document.createElement('input');
                            input.type = 'hidden';
    
                            //enregistrement de la carte utilisée (si demandé)
                            if($("#checkbox-remember").prop("checked")) {
    
                                $.ajax({
    
                                    type: "POST",
                                    url: "/panier/sauvecarte",
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
                            }

                            else {

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
      })

      $("#label-conditions").on("click", function() {

        $("#bouton-paiement").popover('hide');
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

    function loader(show) {

        if(show) {

            $("#loader").show();
        }

        else {
            
            $("#loader").hide();
        }
    }
})