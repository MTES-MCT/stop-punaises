before(() => {
  cy.clearCookie('PHPSESSID')
  Cypress.Cookies.defaults({
    preserve: "PHPSESSID"
  })
});
