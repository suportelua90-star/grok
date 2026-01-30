"use strict";
var KTModalCustomersAdd = (function () {
  var t, e, o, n, r, i;
  return {
    init: function () {
      (i = new bootstrap.Modal(
        document.querySelector("#rainbow_dns")
      )),
        (r = document.querySelector("#rainbow_dns_form")),
        (t = r.querySelector("#rainbow_dns_submit")),
        (e = r.querySelector("#rainbow_dns_cancel")),
        (o = r.querySelector("#rainbow_dns_close")),
        (n = FormValidation.formValidation(r, {
          fields: {
            title: {
              validators: {
                notEmpty: { message: "Title is required" },
              },
            },
            url: {
              validators: {
                notEmpty: { message: "Url is required" },
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
        t.addEventListener("click", function (e) {
          e.preventDefault(),
            n &&
              n.validate().then(function (e) {
                console.log("validated!"),
                  "Valid" == e
                    ? (t.setAttribute("data-kt-indicator", "on"),
                      (t.disabled = !0),
                      setTimeout(function () {
                        t.removeAttribute("data-kt-indicator"),
                          Swal.fire({
                            text: "Form has been successfully submitted!",
                            icon: "success",
                            buttonsStyling: !1,
                            confirmButtonText: "Ok, got it!",
                            customClass: { confirmButton: "btn btn-primary" },
                          }).then(function (e) {
                            e.isConfirmed &&
                              (i.hide(),
                              (t.disabled = !1),
                              (window.location =
                                r.getAttribute("data-kt-redirect")));
                          });
                      }, 2e3))
                    : Swal.fire({
                        text: "Sorry, looks like there are some errors detected, please try again.",
                        icon: "error",
                        buttonsStyling: !1,
                        confirmButtonText: "Ok, got it!",
                        customClass: { confirmButton: "btn btn-primary" },
                      });
              });
        }),
        e.addEventListener("click", function (t) {
          t.preventDefault(),
            Swal.fire({
              text: "Are you sure you would like to cancel?",
              icon: "warning",
              showCancelButton: !0,
              buttonsStyling: !1,
              confirmButtonText: "Yes, cancel it!",
              cancelButtonText: "No, return",
              customClass: {
                confirmButton: "btn btn-primary",
                cancelButton: "btn btn-active-light",
              },
            }).then(function (t) {
              t.value
                ? (r.reset(), i.hide())
                : "cancel" === t.dismiss &&
                  Swal.fire({
                    text: "Your form has not been cancelled!.",
                    icon: "error",
                    buttonsStyling: !1,
                    confirmButtonText: "Ok, got it!",
                    customClass: { confirmButton: "btn btn-primary" },
                  });
            });
        }),
        o.addEventListener("click", function (t) {
          t.preventDefault(),
            Swal.fire({
              text: "Are you sure you would like to cancel?",
              icon: "warning",
              showCancelButton: !0,
              buttonsStyling: !1,
              confirmButtonText: "Yes, cancel it!",
              cancelButtonText: "No, return",
              customClass: {
                confirmButton: "btn btn-primary",
                cancelButton: "btn btn-active-light",
              },
            }).then(function (t) {
              t.value
                ? (r.reset(), i.hide())
                : "cancel" === t.dismiss &&
                  Swal.fire({
                    text: "Your form has not been cancelled!.",
                    icon: "error",
                    buttonsStyling: !1,
                    confirmButtonText: "Ok, got it!",
                    customClass: { confirmButton: "btn btn-primary" },
                  });
            });
        });
    },
  };
})();
KTUtil.onDOMContentLoaded(function () {
  KTModalCustomersAdd.init();
});
