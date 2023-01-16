var cookiePHPSESSID = null;

before(() => {
  cy.clearCookie('PHPSESSID')
});