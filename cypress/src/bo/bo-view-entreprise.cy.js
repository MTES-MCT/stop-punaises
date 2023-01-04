describe('Go to a entreprise', () => {
  it ('Displays one entreprise', () => {
    cy.get('.liste-entreprises tr .fr-btn.fr-icon-arrow-right-fill').first().click()
    cy.wait(300)
    cy.get('.fr-container h2').contains('Employés')
  })

})
