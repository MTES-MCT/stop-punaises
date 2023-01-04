describe('Go to list of historique', () => {
  it ('Displays the list of historique', () => {
    cy.get('.fr-nav__list').contains('Historique').click()
    cy.wait(300)
    cy.get('.fr-container h1').contains('Données historiques')
  })

})
