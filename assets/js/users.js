"use strict";
var KTSigninGeneral = (function () {
  var t, e, r;
  return {
    init: function () {
      (t = document.querySelector("#kt_sign_in_form")),
        (e = document.querySelector("#kt_sign_in_submit")),
        (r = FormValidation.formValidation(t, {
          fields: {
            username: {
              validators: {
                notEmpty: { message: "The username is required" },
              },
            },
            password: {
              validators: {
                notEmpty: { message: "The password is required" },
              },
            },
          },
          plugins: {
            trigger: new FormValidation.plugins.Trigger(),
            bootstrap: new FormValidation.plugins.Bootstrap5({
              rowSelector: ".fv-row",
              eleInvalidClass: "",
              eleValidClass: "",
            }),
          },
        })),
        e.addEventListener("click", function (i) {
          i.preventDefault(),
            r.validate().then(function (r) {
              if ("Valid" === r) {
                e.setAttribute("data-kt-indicator", "on"),
                  (e.disabled = !0);

                fetch("authenticate.php", {
                  method: "POST",
                  headers: { "Content-Type": "application/x-www-form-urlencoded" },
                  body: new URLSearchParams({
                    username: t.querySelector('[name="username"]').value,
                    password: t.querySelector('[name="password"]').value,
                  }).toString(),
                })
                  .then((response) => response.json())
                  .then((data) => {
                    e.removeAttribute("data-kt-indicator"), (e.disabled = !1);

                    if (data.success) {
                      Swal.fire({
                        text: data.message,
                        icon: "success",
                        buttonsStyling: !1,
                        confirmButtonText: "Ok, got it!",
                        customClass: { confirmButton: "btn btn-primary" },
                      }).then(() => {
                        window.location.href = "dns.php";
                      });
                    } else {
                      Swal.fire({
                        text: data.message,
                        icon: "error",
                        buttonsStyling: !1,
                        confirmButtonText: "Try again",
                        customClass: { confirmButton: "btn btn-primary" },
                      });
                    }
                  })
                  .catch((error) => {
                    e.removeAttribute("data-kt-indicator"), (e.disabled = !1);
                    Swal.fire({
                      text: "An error occurred while processing your request. Please try again.",
                      icon: "error",
                      buttonsStyling: !1,
                      confirmButtonText: "Ok, got it!",
                      customClass: { confirmButton: "btn btn-primary" },
                    });
                    console.error("Error:", error);
                  });
              } else {
                Swal.fire({
                  text: "Sorry, looks like there are some errors detected, please try again.",
                  icon: "error",
                  buttonsStyling: !1,
                  confirmButtonText: "Ok, got it!",
                  customClass: { confirmButton: "btn btn-primary" },
                });
              }
            });
        });
    },
  };
})();
KTUtil.onDOMContentLoaded(function () {
  KTSigninGeneral.init();
});
