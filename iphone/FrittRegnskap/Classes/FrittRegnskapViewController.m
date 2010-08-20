//
//  FrittRegnskapViewController.m
//  FrittRegnskap
//
//  Created by Knut Erik Borgen on 08.08.10.
//  Copyright Knubo Borgen 2010. All rights reserved.
//

#import "FrittRegnskapViewController.h"

#import "MainView.h"

@implementation FrittRegnskapViewController


/*
// The designated initializer. Override to perform setup that is required before the view is loaded.
- (id)initWithNibName:(NSString *)nibNameOrNil bundle:(NSBundle *)nibBundleOrNil {
    if ((self = [super initWithNibName:nibNameOrNil bundle:nibBundleOrNil])) {
        // Custom initialization
    }
    return self;
}
*/

/*
// Implement loadView to create a view hierarchy programmatically, without using a nib.
- (void)loadView {
}
*/



// Implement viewDidLoad to do additional setup after loading the view, typically from a nib.
- (void)viewDidLoad {
	doReload = true;
}

/*
// Override to allow orientations other than the default portrait orientation.
- (BOOL)shouldAutorotateToInterfaceOrientation:(UIInterfaceOrientation)interfaceOrientation {
    // Return YES for supported orientations
    return (interfaceOrientation == UIInterfaceOrientationPortrait);
}
*/

- (void)didReceiveMemoryWarning {
	// Releases the view if it doesn't have a superview.
    [super didReceiveMemoryWarning];
	
	// Release any cached data, images, etc that aren't in use.
}

- (void)viewDidUnload {
	// Release any retained subviews of the main view.
	// e.g. self.myOutlet = nil;

}

- (void)dealloc {


    [super dealloc];
}

- (void) flagDataAsReloaded {
	doReload = true;
}

- (IBAction)showMemberships:(id)sender {
	[membershipViewUIController initView];
	
	[self setToolbarHidden:false animated:true];
	[UIView beginAnimations:nil context:NULL];
	[UIView setAnimationDuration:1.0];
	
	[UIView setAnimationTransition:UIViewAnimationTransitionCurlDown forView:self.view cache:NO];
	
	[self pushViewController:membershipViewUIController animated:false];
	
	[UIView commitAnimations];
}

- (IBAction)showPersons:(id)sender {
	
	if(doReload) {
		doReload = false;
		[personViewController loadPeople];
	}
	
	[UIView beginAnimations:nil context:NULL];
	[UIView setAnimationDuration:1.0];

	[UIView setAnimationTransition:UIViewAnimationTransitionCurlDown forView:self.view cache:NO];
	
	[self pushViewController:personViewController animated:false];
	


	[UIView commitAnimations];
}		


- (void) showPersonDetail:(Person*)person {
	[self pushViewController:personDetailsController animated:true];
	[personDetailsController showPersonDetail:person];
}

- (void)navigationBar:(UINavigationBar *)navigationBar didPopItem:(UINavigationItem *)item {
	[self setToolbarHidden:true animated:false];
}



@end
