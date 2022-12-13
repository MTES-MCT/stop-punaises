import user from '../../fixtures/user.json'

describe('Go to front page and post code postal', () => {
  it ('Validates code postal', () => {
    cy.visit('http://localhost:8090/')
    cy.custom.disableSmoothScroll()
    cy.get('#code-postal').type(user.codepostal)
    cy.get('.btn-next').click()
    cy.wait(1000)
  })

})
