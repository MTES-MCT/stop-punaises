import entreprise from '../../fixtures/entreprise.json'

describe('Go to dashboard', () => {
  it ('Logins with entreprise account', () => {
    cy.get('header .fr-header__body .fr-btns-group .fr-icon-lock-line').click()
    cy.wait(300)
    cy.get('#login-email').type(entreprise.login)
    cy.get('#login-password').type(entreprise.password)
    cy.get('.fr-icon-check-line').click()
    cy.wait(300)
    cy.get('.fr-card').first().click()
  })

})
