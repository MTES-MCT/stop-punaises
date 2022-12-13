describe('Go to front page and post code postal', () => {
  it ('Validates code postal', () => {
    cy.visit('http://localhost:8090/')
    cy.custom.disableSmoothScroll()
    cy.get('#code-postal').type(13006)
    cy.get('.btn-next').click()
    cy.wait(1000)
  })

})
