import user from '../../fixtures/user.json'

describe('Go to front page and post refused code postal', () => {
  it ('Refuses code postal', () => {
    cy.visit('http://localhost:8090/')
    cy.custom.disableSmoothScroll()
    cy.get('#code-postal').type(user.codepostalRefused)
    cy.get('.btn-next').click()
    cy.wait(1000)
    cy.get('.if-territory-not-open').should('be.visible')
  })

})
