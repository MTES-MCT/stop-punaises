describe('Check of items on dashboard for admin', () => {
  it ('Displays the header items', () => {
    cy.get('.fr-header__tools-links').contains('Tableau de bord')
    cy.get('.fr-header__tools-links').contains('Cartographie')
    cy.get('.fr-header__tools-links').contains('Déconnexion')
  })
  it ('Displays the menu items', () => {
    cy.get('.fr-nav__list').contains('Signalements')
    cy.get('.fr-nav__list').contains('Historique')
    cy.get('.fr-nav__list').contains('Hors périmètre')
    cy.get('.fr-nav__list').contains('Les entreprises')
  })
  it ('Displays the title', () => {
    cy.get('.fr-container h1').contains('Tableau de bord administrateur')
  })
  it ('Displays the cards', () => {
    cy.get('.fr-card__title').contains('Signalements à traiter')
    cy.get('.fr-card__title').contains('Hors périmètre')
    cy.get('.fr-card__title').contains('Données historiques')
    cy.get('.fr-card__title').contains('Les entreprises')
  })

})
