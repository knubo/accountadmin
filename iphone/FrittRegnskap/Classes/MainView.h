//
//  MainView.h
//
//  Created by Knut Erik Borgen on 08.08.10.
//  Copyright 2010 Knubo Borgen. All rights reserved.
//

#import <UIKit/UIKit.h>
#import <Foundation/Foundation.h>
#import "SetupView.h"
#import "PersonView.h"

@interface MainView : UIView {
    IBOutlet SetupView *config;
	IBOutlet PersonView *personView;
}
- (IBAction)hideConfig:(id)sender;
- (IBAction)showConfig:(id)sender;
- (IBAction)showPersons:(id)sender;
- (IBAction)hidePersons:(id)sender;

@end