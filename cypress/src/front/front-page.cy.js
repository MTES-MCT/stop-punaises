describe('Go to front page', { testIsolation: false }, () => {
  it ('Checks we are on front page', () => {
    cy.visit('http://localhost:8090/')
    cy.custom.disableSmoothScroll()
  })

})
