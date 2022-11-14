const disableSmoothScroll = () => {
  cy.document().then(document => {
    const node = document.createElement('style');
    node.innerHTML = 'html { scroll-behavior: inherit !important; }';
    document.body.appendChild(node);
  });
};

before(() => {
  cy.clearCookie('PHPSESSID')
  Cypress.Cookies.defaults({
    preserve: "PHPSESSID"
  })
});

describe('Test the front Signalement interface and get level infestation 0', () => {
  it('Displays the front page', () => {
    cy.visit('http://localhost:8090/')
    disableSmoothScroll()
    cy.get('#code-postal')
  })

  it ('Displays the front Signalement page', () => {
    cy.get('#code-postal').type(13006)
    cy.get('.btn-next').click()
    cy.wait(1000)
    cy.get('#step-info_intro').should('be.visible')
  })

  it ('Gets 0 as a result', () => {
    cy.get('section.current-step .btn-next').click()
    cy.wait(300)
    cy.get('#signalement_front_typeLogement_1').click()
    cy.get('#signalement_front_superficie').type(45)
    cy.get('.skip-search-address').click()
    cy.get('#signalement_front_adresse').type("5 rue d'Italie")
    cy.get('#signalement_front_codePostal').type(13006)
    cy.get('#signalement_front_ville').type('Marseille')
    cy.get('section.current-step .btn-next').click()
    cy.wait(300)
    cy.get('#signalement_front_dureeInfestation_0').click()
    cy.get('section.current-step .btn-next').click()
    cy.wait(300)
    cy.get('section.current-step .btn-next').click()
    cy.wait(300)
    cy.get('#signalement_front_piquresExistantes_1').click()
    cy.get('section.current-step .btn-next').click()
    cy.wait(300)
    cy.get('#signalement_front_dejectionsTrouvees_1').click()
    cy.get('section.current-step .btn-next').click()
    cy.wait(300)
    cy.get('section.current-step .btn-next').click()
    cy.wait(300)
    cy.get('#signalement_front_oeufsEtLarvesTrouves_1').click()
    cy.get('section.current-step .btn-next').click()
    cy.wait(300)
    cy.get('#signalement_front_punaisesTrouvees_1').click()
    cy.get('section.current-step .btn-next').click()
    cy.wait(300)
    cy.get('#signalement_front_nomOccupant').type('Fragione')
    cy.get('#signalement_front_prenomOccupant').type('Philippe')
    cy.get('#signalement_front_telephoneOccupant').type('0612345678')
    cy.get('#signalement_front_emailOccupant').type('akh@gmail.com')
    cy.get('section.current-step .btn-next').click()
    cy.wait(300)
    cy.get('#niveau-infestation .niveau-infestation').contains('0')
  })

})
