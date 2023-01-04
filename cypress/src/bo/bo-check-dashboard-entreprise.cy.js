describe('Check of items on dashboard for entreprise', () => {
  it ('Displays the header items', () => {
    cy.get('.fr-header__tools-links').contains('Tableau de bord')
    cy.get('.fr-header__tools-links').contains('Mon entreprise')
    cy.get('.fr-header__tools-links').contains('Déconnexion')
  })
  it ('Displays the menu items', () => {
    cy.get('.fr-nav__list').contains('Signalements')
    cy.get('.fr-nav__list').contains('Historique')
  })
  it ('Displays the title', () => {
    cy.get('.fr-container h1').contains('Tableau de bord entreprise')
  })
  it ('Displays the cards', () => {
    cy.get('.fr-card__title').contains('Signalements à traiter')
    cy.get('.fr-card__title').contains('Données historiques')
    cy.get('.fr-card__title').contains('Mon entreprise')
  })

})
