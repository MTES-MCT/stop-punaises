describe('Go to front page', () => {
  it ('Checks we are on front page', () => {
    cy.visit('http://localhost:8090/')
    cy.custom.disableSmoothScroll()
  })

})
