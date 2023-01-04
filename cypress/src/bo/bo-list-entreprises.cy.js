describe('Go to list of entreprises', () => {
  it ('Displays the list of entreprises', () => {
    cy.get('.fr-nav__list').contains('Les entreprises').click()
    cy.wait(300)
    cy.get('.fr-container h1').contains('Les entreprises partenaires')
  })

})
