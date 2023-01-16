import admin from '../../fixtures/admin.json'

describe('Go to dashboard', { testIsolation: false }, () => {
  it ('Logins with admin account', () => {
    cy.get('header .fr-header__body .fr-btns-group .fr-icon-lock-line').click()
    cy.wait(300)
    cy.get('#login-email').type(admin.login)
    cy.get('#login-password').type(admin.password)
    cy.get('.fr-icon-check-line').click()
    cy.wait(300)
  })

})
